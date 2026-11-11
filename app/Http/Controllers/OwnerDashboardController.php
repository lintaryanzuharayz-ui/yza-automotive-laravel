<?php

namespace App\Http\Controllers;

use App\Models\{Mobil, PenjualanMobil, Petugas};
use App\Services\{OwnerAnalyticsService, SalesAwardsService};
use Carbon\Carbon;
use Illuminate\Http\{RedirectResponse, Request, Response};
use Illuminate\View\View;
use Throwable;

class OwnerDashboardController extends Controller
{
    public function dashboard(Request $request, OwnerAnalyticsService $analytics, SalesAwardsService $awards): View
    {
        $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('from') ? 'after_or_equal:from' : null]),
        ]);
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $awardYear = now('Asia/Jakarta')->year;
        $overview = app(\App\Services\OwnerOverviewService::class);
        $ownerInventory = $overview->inventory();
        $ownerActivities = $overview->activities($from, $to, $analytics);
        return view('owner.dashboard', compact('ownerInventory', 'ownerActivities') + ['period' => ['from' => $from, 'to' => $to], 'kpis' => $analytics->kpis($from, $to), 'monthlySales' => $analytics->monthlySales($from, $to), 'brands' => $analytics->brandBreakdown($from, $to), 'models' => $analytics->modelBreakdown($from, $to), 'bookingSales' => $analytics->bookingSalesTrend($from, $to), 'serviceTrends' => $analytics->serviceTrends($from, $to), 'performance' => $analytics->salesPerformance($from, $to), 'topPerformers' => array_slice($awards->leaderboard($awardYear), 0, 3), 'service' => $analytics->serviceSummary($from, $to), 'payments' => $analytics->paymentSummary($from, $to), 'inventory' => $analytics->inventorySummary()]);
    }

    public function serviceReport(Request $request, OwnerAnalyticsService $analytics): View
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $filters = $request->validate(['status' => ['nullable', 'string', 'max:30'], 'mechanic' => ['nullable', 'integer']]);
        $rows = $analytics->serviceReports($from, $to, $filters)->paginate(25)->withQueryString();
        $mechanics = Petugas::whereHas('user.role', fn ($q) => $q->where('nama_role', 'mechanic'))->where('status', 'aktif')->orderBy('nama_lengkap')->get();
        return view('owner.service-report', compact('rows', 'mechanics', 'filters') + ['period' => ['from' => $from, 'to' => $to], 'trend' => $analytics->serviceTrends($from, $to), 'total' => (clone $analytics->serviceReports($from, $to, $filters))->count(), 'completed' => (clone $analytics->serviceReports($from, $to, $filters))->whereIn('status', ['selesai', 'ditutup'])->count()]);
    }

    public function salesPerformanceReport(Request $request, OwnerAnalyticsService $analytics): View
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        return view('owner.performance-report', ['period' => ['from' => $from, 'to' => $to], 'performance' => $analytics->salesPerformance($from, $to)]);
    }

    public function paymentReport(Request $request, OwnerAnalyticsService $analytics): View
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'method' => ['nullable', 'string', 'max:40'], 'status' => ['nullable', 'string', 'max:30'], 'date_basis' => ['nullable', 'in:tanggal,verified_at']]);
        $summary = $analytics->paymentReport($from, $to, $filters);
        $gateway = $analytics->paymentSummary($from, $to);
        $methods = collect($summary['rows'])->pluck('metode')->filter()->unique()->sort()->values();
        $statuses = collect($summary['rows'])->pluck('status')->filter()->unique()->sort()->values();
        $distribution = collect($summary['rows'])->groupBy('metode')->map(fn ($rows, $method) => ['label' => $method, 'amount' => (float) $rows->sum('jumlah'), 'count' => $rows->count()])->sortByDesc('amount')->values();
        $today = $analytics->paymentReport(now('Asia/Jakarta')->startOfDay(), now('Asia/Jakarta')->endOfDay());
        $operations = ['today' => (float) $today['verified'], 'refund_pending' => (float) $gateway['pending_refunds'], 'verified_receipt' => (float) $gateway['net_verified'], 'review' => (int) $analytics->paymentReport($from, $to, ['status' => 'menunggu'])['rows']->count()];
        return view('owner.payment-report', compact('filters', 'summary', 'gateway', 'methods', 'statuses', 'distribution', 'operations') + ['period' => ['from' => $from, 'to' => $to]]);
    }

    public function inventoryReport(Request $request, OwnerAnalyticsService $analytics): View
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $rows = $analytics->inventoryRows()->paginate(25)->withQueryString();
        return view('owner.inventory-report', ['period' => ['from' => $from, 'to' => $to], 'rows' => $rows, 'summary' => $analytics->inventorySummary()]);
    }

    public function salesAwards(Request $request, SalesAwardsService $awards): View
    {
        $year = $awards->year((int) $request->query('year', now('Asia/Jakarta')->year));
        $award = $awards->award($year);
        return view('owner.sales-awards', ['year' => $year, 'award' => $award, 'leaderboard' => $awards->leaderboard($year), 'history' => \App\Models\SalesAward::where('award_year', '<>', $year)->where('status', 'finalized')->with(['results.sales', 'finalizer'])->latest('award_year')->get(), 'method' => $awards->methodSnapshot()]);
    }

    public function finalizeSalesAwards(Request $request, SalesAwardsService $awards): RedirectResponse
    {
        abort_unless($request->user()->roleKey() === 'super_admin', 403);
        $data = $request->validate(['year' => ['required', 'integer', 'min:2000', 'max:'.now('Asia/Jakarta')->year]]);
        try { $awards->finalize((int) $data['year'], (int) $request->user()->petugas?->id_petugas); return back()->with('status', 'Sales Excellence Awards berhasil difinalisasi dan diaudit.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function exportSalesAwards(Request $request, SalesAwardsService $awards): Response
    {
        $year = $awards->year((int) $request->query('year', now('Asia/Jakarta')->year));
        $award = $awards->award($year);
        $rows = collect($awards->leaderboard($year))->map(fn ($row) => [$row['rank'], $row['name'], $row['units'], $row['target_units'] ?? 'Belum tersedia', $row['achievement'] === null ? 'Belum tersedia' : $row['achievement'].'%', $row['amount'], $row['eligibility']])->all();
        return $this->tableExport($request, 'Sales Excellence Awards '.$year.' ('.strtoupper($award->status).')', ['Ranking','Sales','Unit final','Target unit','Pencapaian','Nilai final','Kelayakan'], $rows, Carbon::create($year, 1, 1), Carbon::create($year, 12, 31), 'sales-awards-'.$year);
    }

    public function exportService(Request $request, OwnerAnalyticsService $analytics): Response
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $filters = $request->validate(['status' => ['nullable', 'string', 'max:30'], 'mechanic' => ['nullable', 'integer']]);
        $rows = $analytics->serviceReports($from, $to, $filters)->get()->map(fn ($row) => [$row->kode_servis, $row->kendaraan?->merk.' '.$row->kendaraan?->model, $row->pelanggan?->nama_lengkap, $row->bookingServis?->jenisServis?->nama_servis, $row->petugas?->nama_lengkap ?: '—', $row->tanggal_masuk?->setTimezone('Asia/Jakarta')->format('d M Y H:i'), $row->tanggal_selesai?->setTimezone('Asia/Jakarta')->format('d M Y H:i') ?: '—', $row->status, (float) $row->total_biaya])->all();
        return $this->tableExport($request, 'Laporan Servis', ['Work Order','Kendaraan','Pelanggan','Jenis servis','Mekanik utama','Tanggal masuk','Tanggal selesai','Status','Total'], $rows, $from, $to, 'servis');
    }

    public function exportSalesPerformance(Request $request, OwnerAnalyticsService $analytics): Response
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $rows = collect($analytics->salesPerformance($from, $to))->map(fn ($row) => [$row['name'], $row['leads'], $row['test_drives'], $row['orders'], $row['units'], $row['amount'], $row['target_units'], $row['achievement'].'%'])->all();
        return $this->tableExport($request, 'Performa Sales', ['Sales','Prospek','Test drive','Pesanan','Unit final','Nilai final','Target unit','Pencapaian'], $rows, $from, $to, 'performa-sales');
    }

    public function exportPayment(Request $request, OwnerAnalyticsService $analytics): Response
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'method' => ['nullable', 'string', 'max:40'], 'status' => ['nullable', 'string', 'max:30'], 'date_basis' => ['nullable', 'in:tanggal,verified_at']]);
        $rows = $analytics->paymentReport($from, $to, $filters)['rows']->map(fn ($row) => [$row->kode_pembayaran, $row->tanggal?->setTimezone('Asia/Jakarta')->format('d M Y H:i'), $row->metode, $row->status, (float) $row->jumlah, in_array(strtolower((string) $row->metode), ['midtrans', 'online'], true) ? 'Gateway' : 'Kasir / Offline'])->all();
        return $this->tableExport($request, 'Pendapatan dan Pembayaran', ['Kode pembayaran','Tanggal','Metode','Status','Jumlah','Sumber'], $rows, $from, $to, 'pendapatan-pembayaran');
    }

    public function exportInventory(Request $request, OwnerAnalyticsService $analytics): Response
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $rows = $analytics->inventoryRows()->get()->map(fn ($row) => [$row->kode_mobil, $row->merk, $row->model, $row->tahun, $row->status, (float) $row->harga_jual, $row->tanggal_masuk?->format('d M Y')])->all();
        return $this->tableExport($request, 'Inventori Kendaraan', ['Kode','Merek','Model','Tahun','Status','Harga jual','Tanggal masuk'], $rows, $from, $to, 'inventori-kendaraan');
    }

    private function tableExport(Request $request, string $title, array $headers, array $rows, Carbon $from, Carbon $to, string $slug): Response
    {
        if ($request->query('format') === 'pdf') return response($this->pdfTable($title, $headers, $rows, $from, $to), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="yza-'.$slug.'.pdf"']);
        return response($this->xlsxTable($title, $headers, $rows, $from, $to), 200, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Content-Disposition' => 'attachment; filename="yza-'.$slug.'.xlsx"']);
    }

    private function xlsxTable(string $title, array $headers, array $rows, Carbon $from, Carbon $to): string
    {
        $esc = fn ($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $row = function (array $values) use ($esc): string { $xml = '<row>'; foreach ($values as $value) { $xml .= is_int($value) || is_float($value) ? '<c t="n"><v>'.(float) $value.'</v></c>' : '<c t="inlineStr"><is><t>'.$esc($value).'</t></is></c>'; } return $xml.'</row>'; };
        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$row(['PT YZA Automotive — '.$title]).$row(['Periode '.$from->format('d M Y').' - '.$to->format('d M Y')]).$row($headers);
        foreach ($rows as $item) $sheet .= $row($item); $sheet .= '</sheetData></worksheet>';
        return $this->zipStore(['[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>', '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>', 'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets></workbook>', 'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>', 'xl/worksheets/sheet1.xml' => $sheet]);
    }

    private function pdfTable(string $title, array $headers, array $rows, Carbon $from, Carbon $to): string
    {
        $lines = ['PT YZA Automotive - '.$title, 'Periode: '.$from->format('d M Y').' - '.$to->format('d M Y'), implode(' | ', $headers)];
        foreach ($rows as $row) $lines[] = implode(' | ', array_map(fn ($value) => is_numeric($value) && (float) $value > 1000 ? 'Rp '.number_format((float) $value, 0, ',', '.') : (string) $value, $row));
        $stream = "BT\n/F1 8 Tf\n30 760 Td\n"; foreach (array_slice($lines, 0, 48) as $line) $stream .= '('.str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], substr($line, 0, 130)).") Tj\n0 -15 Td\n"; $stream .= 'ET';
        $objects = ["<< /Type /Catalog /Pages 2 0 R >>", "<< /Type /Pages /Kids [3 0 R] /Count 1 >>", "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>", "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>", "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream"];
        $pdf = "%PDF-1.4\n"; $offsets = [0]; foreach ($objects as $number => $object) { $offsets[$number + 1] = strlen($pdf); $pdf .= ($number + 1)." 0 obj\n{$object}\nendobj\n"; } $xref = strlen($pdf); $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n"; for ($i = 1; $i <= count($objects); $i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]); return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    public function salesReport(Request $request, OwnerAnalyticsService $analytics): View
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $filters = $request->validate(['sales' => ['nullable', 'integer'], 'brand' => ['nullable', 'string', 'max:80'], 'model' => ['nullable', 'string', 'max:100'], 'q' => ['nullable', 'string', 'max:120']]);
        $scope = $analytics->finalVehicleSales($from, $to, $filters);
        $totalUnits = (clone $scope)->count();
        $totalValue = (float) (clone $scope)->sum('total_akhir');
        $verifiedPayments = (clone $scope)->whereHas('transaksi.pembayarans', fn ($q) => $q->where('arah', 'masuk')->where('status', 'terverifikasi'))->count();
        $handedOver = (clone $scope)->whereHas('memberOrder', fn ($q) => $q->where('status', 'diserahkan'))->count();
        $today = now('Asia/Jakarta')->toDateString();
        $todayTransactions = (clone $scope)->whereDate('tanggal_penjualan', $today)->count();
        $brands = (clone $scope)->join('mobil as sales_report_mobil', 'sales_report_mobil.id_mobil', '=', 'penjualan_mobil.id_mobil')->select('sales_report_mobil.merk')->distinct()->orderBy('sales_report_mobil.merk')->pluck('sales_report_mobil.merk');
        return view('owner.sales-report', [
            'rows' => $scope->latest('tanggal_penjualan')->paginate(25)->withQueryString(),
            'period' => ['from' => $from, 'to' => $to],
            'salesPeople' => Petugas::whereHas('user.role', fn ($q) => $q->where('nama_role', 'sales'))->where('status', 'aktif')->orderBy('nama_lengkap')->get(),
            'brands' => $brands,
            'models' => Mobil::distinct()->orderBy('model')->pluck('model'),
            'filters' => $filters,
            'totalUnits' => $totalUnits,
            'totalValue' => $totalValue,
            'verifiedPayments' => $verifiedPayments,
            'handedOver' => $handedOver,
            'todayTransactions' => $todayTransactions,
            'averageValue' => $totalUnits > 0 ? $totalValue / $totalUnits : null,
            'brandsBreakdown' => $analytics->brandBreakdown($from, $to, $filters),
        ]);
    }

    public function exportSales(Request $request, OwnerAnalyticsService $analytics): Response
    {
        [$from, $to] = $analytics->period($request->query('from'), $request->query('to'));
        $filters = $request->validate(['sales' => ['nullable', 'integer'], 'brand' => ['nullable', 'string', 'max:80'], 'model' => ['nullable', 'string', 'max:100'], 'q' => ['nullable', 'string', 'max:120']]);
        $rows = $analytics->finalVehicleSales($from, $to, $filters)->latest('tanggal_penjualan')->get();
        $format = $request->query('format', 'excel');
        if ($format === 'pdf') return response($this->pdf($rows, $from, $to), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="yza-rekap-penjualan.pdf"']);
        return response($this->xlsx($rows, $from, $to), 200, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Content-Disposition' => 'attachment; filename="yza-rekap-penjualan.xlsx"']);
    }

    private function spreadsheetXml($rows, Carbon $from, Carbon $to): string
    {
        $cell = fn ($value) => '<Cell><Data ss:Type="String">'.e((string) $value).'</Data></Cell>';
        $xml = '<?xml version="1.0"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Rekap Penjualan"><Table>';
        $xml .= '<Row>'.$cell('PT YZA Automotive — Rekap Penjualan').'</Row><Row>'.$cell('Periode '.$from->format('d M Y').' - '.$to->format('d M Y')).'</Row><Row>'.$cell('No transaksi').$cell('Tanggal').$cell('Pelanggan').$cell('Kendaraan').$cell('Sales').$cell('Total').$cell('Status').'</Row>';
        foreach ($rows as $row) $xml .= '<Row>'.$cell($row->transaksi?->kode_transaksi ?: $row->kode_penjualan).$cell($row->tanggal_penjualan?->setTimezone('Asia/Jakarta')->format('d M Y H:i')).$cell($row->pelanggan?->nama_lengkap ?: '—').$cell(trim(($row->mobil?->merk ?: '').' '.($row->mobil?->model ?: ''))).$cell($row->sales?->nama_lengkap ?: 'Digital / belum ditugaskan').$cell(number_format((float)$row->total_akhir, 0, ',', '.')).$cell('Selesai').'</Row>';
        return $xml.'</Table></Worksheet></Workbook>';
    }

    private function xlsx($rows, Carbon $from, Carbon $to): string
    {
        $esc = fn ($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $row = function (array $values) use ($esc): string { $xml = '<row>'; foreach ($values as $value) { $xml .= is_int($value) || is_float($value) ? '<c t="n"><v>'.(float) $value.'</v></c>' : '<c t="inlineStr"><is><t>'.$esc($value).'</t></is></c>'; } return $xml.'</row>'; };
        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        $sheet .= $row(['PT YZA Automotive — Rekap Penjualan']).$row(['Periode '.$from->format('d M Y').' - '.$to->format('d M Y')]).$row(['No transaksi','Tanggal','Pelanggan','Kendaraan','Sales','Nominal','Pembayaran','Serah-terima']);
        foreach ($rows as $item) $sheet .= $row([$item->transaksi?->kode_transaksi ?: $item->kode_penjualan, $item->tanggal_penjualan?->setTimezone('Asia/Jakarta')->format('d M Y H:i'), $item->pelanggan?->nama_lengkap ?: '—', trim(($item->mobil?->merk ?: '').' '.($item->mobil?->model ?: '')), $item->sales?->nama_lengkap ?: 'Digital / belum ditugaskan', (float) $item->total_akhir, $this->paymentLabel($item), $this->handoverLabel($item)]);
        $sheet .= '</sheetData></worksheet>';
        $files = ['[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>', '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>', 'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Rekap Penjualan" sheetId="1" r:id="rId1"/></sheets></workbook>', 'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>', 'xl/worksheets/sheet1.xml' => $sheet];
        return $this->zipStore($files);
    }

    private function zipStore(array $files): string
    {
        $local = ''; $central = ''; $offset = 0; $count = 0;
        foreach ($files as $name => $content) { $name = (string) $name; $crc = (int) sprintf('%u', crc32($content)); $size = strlen($content); $nameSize = strlen($name); $local .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $size, $size, $nameSize, 0).$name.$content; $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0, $crc, $size, $size, $nameSize, 0, 0, 0, 0, 0, $offset).$name; $offset += strlen($local) - $offset; $count++; }
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, $count, $count, strlen($central), strlen($local), 0);
        return $local.$central.$end;
    }

    private function pdf($rows, Carbon $from, Carbon $to): string
    {
        $lines = ['PT YZA Automotive - Rekap Penjualan', 'Periode: '.$from->format('d M Y').' - '.$to->format('d M Y'), ''];
        $lines[] = 'Transaksi | Tanggal | Kendaraan | Nominal | Pembayaran | Serah-terima';
        foreach ($rows as $row) $lines[] = sprintf('%s | %s | %s | Rp %s | %s | %s', $row->transaksi?->kode_transaksi ?: $row->kode_penjualan, $row->tanggal_penjualan?->setTimezone('Asia/Jakarta')->format('d-m-Y'), trim(($row->mobil?->merk ?: '').' '.($row->mobil?->model ?: '')), number_format((float)$row->total_akhir, 0, ',', '.'), $this->paymentLabel($row), $this->handoverLabel($row));
        $stream = "BT\n/F1 10 Tf\n40 760 Td\n";
        foreach (array_slice($lines, 0, 48) as $index => $line) $stream .= '('.str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], substr($line, 0, 115)).") Tj\n0 -15 Td\n";
        $stream .= 'ET';
        $objects = ["<< /Type /Catalog /Pages 2 0 R >>", "<< /Type /Pages /Kids [3 0 R] /Count 1 >>", "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>", "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>", "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream"];
        $pdf = "%PDF-1.4\n"; $offsets = [0]; foreach ($objects as $number => $object) { $offsets[$number + 1] = strlen($pdf); $pdf .= ($number + 1)." 0 obj\n{$object}\nendobj\n"; }
        $xref = strlen($pdf); $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n"; for ($i = 1; $i <= count($objects); $i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]); $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF"; return $pdf;
    }

    private function paymentLabel(PenjualanMobil $sale): string
    {
        $payments = $sale->transaksi?->pembayarans ?? collect();
        if ($payments->contains(fn ($payment) => $payment->arah === 'masuk' && $payment->status === 'terverifikasi')) return 'Terverifikasi';
        if ($payments->contains(fn ($payment) => $payment->arah === 'masuk' && in_array($payment->status, ['menunggu', 'pending'], true))) return 'Menunggu';
        if ($payments->contains(fn ($payment) => $payment->arah === 'masuk' && in_array($payment->status, ['ditolak', 'dibatalkan', 'expired'], true))) return 'Ditolak';
        return 'Belum ada pembayaran valid';
    }

    private function handoverLabel(PenjualanMobil $sale): string
    {
        return $sale->memberOrder?->status === 'diserahkan' ? 'Selesai' : 'Selesai operasional';
    }
}
