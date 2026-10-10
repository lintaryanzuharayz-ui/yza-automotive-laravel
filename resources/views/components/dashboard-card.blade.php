<section class="yza-dashboard-card">
    <span class="yza-eyebrow">Laravel migration</span>
    <h1>{{ $area }} Dashboard</h1>
    <p>Authentication dan identity foundation YZA Automotive sedang dimigrasikan secara bertahap.</p>
    <dl class="yza-identity-list">
        <div><dt>Nama</dt><dd>{{ $user->displayName() }}</dd></div>
        <div><dt>Username</dt><dd>{{ $user->username }}</dd></div>
        <div><dt>Role</dt><dd>{{ ucfirst($user->roleName()) }}</dd></div>
    </dl>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="yza-button yza-button-primary" type="submit">Logout</button>
    </form>
</section>
