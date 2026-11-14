<?php
// CLI-only evidence collector. No HTTP requests, seeders, migrations or business mutations.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
$db->statement('SET TRANSACTION READ ONLY');
$db->beginTransaction();
try {
    $roleRows = App\Models\Role::with('permissions')->get();
    $roles = $roleRows->map(fn ($role) => ['stored' => $role->nama_role, 'key' => $role->key(),
        'users' => $role->users()->count(), 'permissions' => $role->permissions->pluck('kode')->sort()->values()->all()])->all();
    $accounts = App\Models\User::with(['role', 'petugas'])->whereHas('role', fn ($q) => $q->whereIn('nama_role', ['admin', 'super_admin']))->get()
        ->map(fn ($user) => ['username' => $user->username, 'display_name' => $user->displayName(), 'stored_role' => $user->roleName(), 'effective_role' => $user->roleKey(), 'status' => $user->status])->all();
    $sidebar = file_get_contents(resource_path('views/components/admin-sidebar.blade.php'));
    preg_match('/@php\s+(.*?)@endphp/s', $sidebar, $match);
    eval($match[1]); // The repository's initial array declaration, not external input.
    $routes = collect(app('router')->getRoutes()->getRoutes())->mapWithKeys(fn ($route) => [(string) $route->getName() => [
        'uri' => $route->uri(), 'methods' => $route->methods(), 'action' => $route->getActionName(), 'middleware' => $route->gatherMiddleware(),
    ]])->all();
    $menus = [];
    foreach ($adminSections as $section) foreach ($section['items'] as $item) {
        $menus[] = ['group' => $section['title'], 'label' => $item['label'], 'route' => $item['route'], 'declared_icon' => $item['icon'],
            'rendered_icon' => class_exists(App\Support\AdminNavigation::class) ? App\Support\AdminNavigation::icon($item['route']) : null,
            'route_definition' => $routes[$item['route']] ?? null];
    }
    $missingActions = [];
    foreach ($routes as $name => $definition) {
        $action = $definition['action'];
        if ($action === 'Closure') continue;
        [$class, $method] = array_pad(explode('@', $action, 2), 2, '__invoke');
        if (!class_exists($class) || !method_exists($class, $method)) $missingActions[] = ['route' => $name, 'action' => $action];
    }
    echo json_encode(['database' => $db->getDatabaseName(), 'mode' => 'TRANSACTION READ ONLY / ROLLBACK', 'roles' => $roles, 'accounts' => $accounts,
        'menu_count' => count($menus), 'menus' => $menus, 'routes' => $routes, 'route_count' => count(app('router')->getRoutes()->getRoutes()), 'missing_actions' => $missingActions,
        'activity_sources' => ['orders_by_status' => App\Models\PesananMobilMember::select('status')->selectRaw('COUNT(*) AS total')->groupBy('status')->get()->toArray(),
            'incoming_pending_payments' => App\Models\Pembayaran::where('arah', 'masuk')->where('status', 'menunggu')->count()]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} finally { $db->rollBack(); }
