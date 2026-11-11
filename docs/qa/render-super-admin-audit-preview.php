<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.mysql.database' => 'yza_automotive_laravel_test', 'app.url' => 'http://127.0.0.1:8000']);
Illuminate\Support\Facades\DB::purge('mysql');
$db = Illuminate\Support\Facades\DB::connection();
if ($db->getDatabaseName() !== 'yza_automotive_laravel_test') throw new RuntimeException('Testing database required');
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $financial = in_array('--financial', $argv, true);
    $request = Illuminate\Http\Request::create('http://127.0.0.1:8000/'.($financial ? 'owner/pendapatan-pembayaran' : 'admin/aktivitas'));
    $request->setLaravelSession(new Illuminate\Session\Store('audit-preview', new Illuminate\Session\ArraySessionHandler(120)));
    $route = app('router')->getRoutes()->getByName($financial ? 'owner.payment-report' : 'admin.aktivitas.index');
    $request->setRouteResolver(fn () => $route);
    $user = App\Models\User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
    Illuminate\Support\Facades\Auth::setUser($user); $request->setUserResolver(fn () => $user);
    app()->instance('request', $request); app('url')->setRequest($request);
    view()->share('errors', new Illuminate\Support\ViewErrorBag());
    $tasks = collect();
    foreach (['menunggu' => 'Verifikasi pesanan QA-001', 'diproses' => 'PDI sedang diperiksa QA-002', 'tindakan' => 'Pengambilan perlu dikonfirmasi', 'selesai' => 'Kendaraan telah diserahkan'] as $status => $title) {
        $tasks->push(['status' => $status, 'resource_type' => 'pesanan_mobil_member', 'resource_id' => $tasks->count() + 1,
            'type' => 'Pesanan', 'priority' => 'Sedang', 'title' => $title, 'detail' => 'Data simulasi untuk pemeriksaan tampilan', 'date' => now(), 'url' => '#workflow-preview']);
    }
    $data = ['tasks' => $tasks, 'history' => collect(), 'stats' => array_fill_keys(['menunggu','diproses','tindakan','selesai'], 1),
        'q' => '', 'type' => '', 'status' => '', 'from' => '', 'to' => ''];
    $html = $financial ? app(App\Http\Controllers\OwnerDashboardController::class)->paymentReport($request, app(App\Services\OwnerAnalyticsService::class))->render()
        : view('admin.activity.index', $data)->render();
    // Keep the static QA page isolated: no live polling, mutation or navigation into operational handlers.
    $html = str_replace('<head>', '<head><script>window.fetch=async()=>new Response("{}",{headers:{"Content-Type":"application/json"}});document.addEventListener("click",e=>{if(e.target.closest("a"))e.preventDefault();});document.addEventListener("submit",e=>e.preventDefault());</script>', $html);
    file_put_contents(public_path($financial ? '__super_admin_report_qa.html' : '__super_admin_audit_qa.html'), $html);
    echo 'Rendered static QA preview from testing DB only';
} finally { $db->rollBack(); }
