<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\{StorePromoMemberRequest, UpdatePromoMemberRequest};
use App\Models\PromoMember;
use App\Models\PromoSimulation;
use App\Services\{MasterAuditService, PromoMemberService, PromoAnalyticsService};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class PromoMemberController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $category = (string) $request->input('category', '');
        $date = (string) $request->input('date', '');
        $perPage = in_array((int) $request->input('perPage', 10), [10, 20, 50], true) ? (int) $request->input('perPage', 10) : 10;
        $statuses = ['aktif', 'nonaktif'];
        $categories = ['semua', 'penjualan_mobil', 'trade_in', 'penjualan_sparepart', 'cuci_mobil', 'servis', 'spk'];
        $categoryLabels = ['semua'=>'Semua Layanan','penjualan_mobil'=>'Penjualan Mobil','trade_in'=>'Trade-In','penjualan_sparepart'=>'Sparepart','cuci_mobil'=>'Car Wash','servis'=>'Servis','spk'=>'SPK'];

        $query = PromoMember::query()->latest('id_promo');
        if (in_array($status, $statuses, true)) $query->where('status', $status);
        if (in_array($category, $categories, true)) $query->where('berlaku_untuk', $category);
        if ($date !== '') $query->whereDate('mulai_at', $date);
        if ($search !== '') $query->where(fn ($q) => $q->where('nama', 'like', "%{$search}%")->orWhere('kode_promo', 'like', "%{$search}%")->orWhere('berlaku_untuk', 'like', "%{$search}%"));
        $items = $query->paginate($perPage)->withQueryString();

        $now = now();
        $total = PromoMember::count();
        $redemptions = DB::table('promo_redemptions')->where('status', 'completed');
        $stats = [
            'total' => $total,
            'active' => PromoMember::where('status', 'aktif')->where('mulai_at', '<=', $now)->where('selesai_at', '>=', $now)->count(),
            'scheduled' => PromoMember::where('status', 'aktif')->where('mulai_at', '>', $now)->count(),
            'ending' => PromoMember::whereBetween('selesai_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count(),
            'usage' => (clone $redemptions)->count(),
        ];
        $distribution = collect($categories)->map(fn ($value) => (object) ['category' => $value, 'label' => $categoryLabels[$value], 'total' => PromoMember::where('berlaku_untuk', $value)->count()])->filter(fn ($row) => $row->total > 0)->sortByDesc('total')->values();
        $distributionTotal = max(1, (int) $distribution->sum('total'));
        $operations = ['activeToday' => $stats['active'], 'pendingReview' => null, 'usage' => (clone $redemptions)->where('used_at', '>=', $now->copy()->startOfMonth())->count(), 'conversion' => null];

        return view('admin.promo-member.index', compact('items', 'stats', 'operations', 'distribution', 'distributionTotal', 'search', 'status', 'category', 'date', 'perPage', 'statuses', 'categories', 'categoryLabels'));
    }
    public function create(Request $request): View { return view('admin.promo-member.form', ['item' => new PromoMember(['jenis_benefit' => $request->query('jenis_benefit','informasi'), 'berlaku_untuk' => $request->query('berlaku_untuk','semua'), 'status' => 'nonaktif', 'minimum_transaksi' => $request->query('minimum_transaksi',0), 'nilai_benefit'=>$request->query('discount_value'), 'maksimum_benefit'=>$request->query('max_discount'), 'approved_spk_simulation_id'=>$request->query('spk_id'), 'spk_score'=>$request->query('spk_score'), 'spk_recommendation'=>$request->query('spk_recommendation'), 'projection_transactions'=>$request->query('projection_transactions'), 'projection_revenue'=>$request->query('projection_revenue'), 'projection_discount'=>$request->query('projection_discount'), 'projection_margin'=>$request->query('projection_margin')])]); }
    public function createFromSimulation(PromoSimulation $promoSimulation): View { $result=$promoSimulation->result_payload??[]; $scope=in_array($promoSimulation->scope,['servis','cuci_mobil','penjualan_sparepart','penjualan_mobil','trade_in'],true)?$promoSimulation->scope:'semua'; return view('admin.promo-member.form',['item'=>new PromoMember(['jenis_benefit'=>$promoSimulation->discount_type==='percentage'?'persentase':'nominal','berlaku_untuk'=>$scope,'status'=>'nonaktif','minimum_transaksi'=>$promoSimulation->min_transaction,'nilai_benefit'=>$promoSimulation->discount_value,'maksimum_benefit'=>$promoSimulation->max_discount,'approved_spk_simulation_id'=>$promoSimulation->simulation_id,'spk_score'=>$promoSimulation->score,'spk_recommendation'=>$promoSimulation->status,'projection_transactions'=>data_get($result,'projection.transactions'),'projection_revenue'=>data_get($result,'projection.revenue'),'projection_discount'=>data_get($result,'projection.discount'),'projection_margin'=>data_get($result,'projection.margin'),'mulai_at'=>now(),'selesai_at'=>now()->addDays(30)]),'simulation'=>$promoSimulation]); }
    public function store(StorePromoMemberRequest $request, PromoMemberService $service, MasterAuditService $audit): RedirectResponse
    {
        $item = new PromoMember();
        $item->forceFill($service->normalize($request->validated(), $request->user()->id_user))->save();
        $audit->record('create', 'promo_member', $item->id_promo, 'Menambah promo member');
        return redirect()->route('admin.promo-member.show', $item)->with('status', 'Promo member dibuat.');
    }
    public function show(PromoMember $promoMember, PromoAnalyticsService $analytics): View { return view('admin.promo-member.show', ['item' => $promoMember, 'analytics' => $analytics->forPromo($promoMember)]); }
    public function edit(PromoMember $promoMember): View { return view('admin.promo-member.form', ['item' => $promoMember]); }
    public function update(UpdatePromoMemberRequest $request, PromoMember $promoMember, PromoMemberService $service, MasterAuditService $audit): RedirectResponse
    {
        $data = $service->normalize($request->validated(), $request->user()->id_user); unset($data['created_by']);
        $promoMember->update($data); $audit->record('update', 'promo_member', $promoMember->id_promo, 'Mengubah promo member');
        return redirect()->route('admin.promo-member.show', $promoMember)->with('status', 'Promo member diperbarui.');
    }
    public function toggle(Request $request, PromoMember $promoMember, MasterAuditService $audit): RedirectResponse
    {
        if ($promoMember->status !== 'aktif' && strtoupper((string)$promoMember->spk_recommendation) === 'BERISIKO' && !$request->boolean('confirm_risk')) return back()->withErrors('Simulasi SPK menilai promo ini berisiko terhadap margin. Konfirmasi override diperlukan.');
        $promoMember->update(['status' => $promoMember->status === 'aktif' ? 'nonaktif' : 'aktif']);
        $audit->record('status', 'promo_member', $promoMember->id_promo, strtoupper((string)$promoMember->spk_recommendation) === 'BERISIKO' ? 'Risk override promo SPK oleh admin' : 'Mengubah status promo member');
        return back();
    }
}
