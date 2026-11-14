<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\{AccountSessionRevoker, RetireDuplicateSuperAdminService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{Artisan, Auth, Crypt, DB, Hash};
use Illuminate\Support\Str;
use Tests\TestCase;

class DuplicateSuperAdminRetirementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('yza:seed-local-staff'); // Explicit isolated testing fixture only.
        if (!User::where('username', 'admin506')->exists()) {
            $user = User::forceCreate(['username' => 'admin506', 'email' => 'retained-admin@example.test',
                'id_role' => \App\Models\Role::where('nama_role', 'admin')->firstOrFail()->getKey(),
                'password' => Hash::make(str_repeat('x', 32)), 'status' => 'aktif']);
            \App\Models\Petugas::create(['id_user' => $user->getKey(), 'kode_petugas' => 'QA-KEEP-'.Str::random(8),
                'nama_lengkap' => 'Retained Admin QA', 'jabatan' => 'Admin', 'status' => 'aktif']);
        }
    }

    private function users(): array
    {
        return [User::where('username', 'admin506')->firstOrFail(), User::where('username', 'superadmin506')->firstOrFail()];
    }

    public function test_retirement_preserves_history_passwords_roles_and_the_retained_account(): void
    {
        [$keep, $target] = $this->users();
        $this->assertNotSame($keep->getKey(), $target->getKey());
        $oldKeep = $keep->getRawOriginal(); $oldTargetPassword = $target->getRawOriginal('password');
        $service = app(RetireDuplicateSuperAdminService::class);
        $before = $service->fingerprints($target->getKey(), $target->petugas->getKey());
        $result = $service->retire($target->getKey(), $keep->getKey());
        $this->assertSame('nonaktif', $target->fresh()->status);
        $this->assertSame('nonaktif', $target->petugas->fresh()->status);
        $this->assertSame($oldKeep, $keep->fresh()->getRawOriginal());
        $this->assertSame($oldTargetPassword, $target->fresh()->getRawOriginal('password'));
        $this->assertSame($before, $service->fingerprints($target->getKey(), $target->petugas->getKey()));
        $backup = json_decode(Crypt::decryptString(file_get_contents($result['backup'])), true);
        $this->assertSame($target->getKey(), $backup['target_user']['id_user']);
        $this->assertSame('aktif', $backup['target_user']['status']);
        $this->assertSame($oldTargetPassword, $backup['target_user']['password']);
        $this->assertSame('super_admin', $keep->fresh()->roleKey());
        $this->actingAs($keep)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($keep)->get(route('admin.petugas.index', ['status' => 'aktif', 'q' => $target->petugas->kode_petugas]))
            ->assertOk()->assertViewHas('items', fn ($items) => !$items->getCollection()->contains('id_user', $target->getKey()));
    }

    public function test_wrong_primary_keys_abort_without_mutation(): void
    {
        [$keep, $target] = $this->users();
        try { app(RetireDuplicateSuperAdminService::class)->retire($keep->getKey(), $target->getKey()); $this->fail('Swapped IDs must fail.'); }
        catch (\RuntimeException $error) { $this->assertStringContainsString('Primary key', $error->getMessage()); }
        $this->assertSame('aktif', $keep->fresh()->status);
        $this->assertSame('aktif', $target->fresh()->status);
        $this->artisan('yza:retire-duplicate-super-admin', ['--apply' => true])->assertExitCode(1);
    }

    public function test_login_and_preexisting_sessions_obey_retirement_without_resetting_retained_password(): void
    {
        [$keep, $target] = $this->users();
        // Known isolated fixture credentials; never reset or guess an operational password.
        $oldKeepPassword = $keep->getRawOriginal('password');
        $fixturePassword = str_repeat('x', 32);
        $this->assertTrue(Hash::check($fixturePassword, $oldKeepPassword));
        $this->post(route('login.store'), ['identity' => $keep->username, 'password' => $fixturePassword])->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk();
        $this->post(route('logout'));
        $this->post(route('login.store'), ['identity' => $target->username, 'password' => $target->username])->assertRedirect(route('admin.dashboard'));
        $oldSessionKey = Auth::guard('web')->getName();
        app(RetireDuplicateSuperAdminService::class)->retire($target->getKey(), $keep->getKey());
        foreach (['admin.dashboard', 'account.password', 'admin.aktivitas.index', 'owner.dashboard'] as $route) {
            Auth::forgetGuards();
            $this->withSession([$oldSessionKey => $target->getKey()])->get(route($route))->assertForbidden();
        }
        Auth::forgetGuards();
        $this->post(route('login.store'), ['identity' => $target->username, 'password' => $target->username])->assertSessionHasErrors('identity');
        $this->assertSame($oldKeepPassword, $keep->fresh()->getRawOriginal('password'));
        Auth::forgetGuards();
        $this->post(route('login.store'), ['identity' => $keep->username, 'password' => $fixturePassword])->assertRedirect(route('admin.dashboard'));
    }

    public function test_encrypted_file_sessions_are_revoked_only_for_the_target(): void
    {
        [$keep, $target] = $this->users();
        $directory = storage_path('framework/testing/retirement-'.Str::uuid());
        mkdir($directory, 0700, true);
        config(['session.driver' => 'file', 'session.files' => $directory, 'session.encrypt' => true]);
        $key = Auth::guard('web')->getName();
        $names = [str_repeat('A', 40), str_repeat('B', 40), str_repeat('C', 40)];
        try {
            file_put_contents($directory.'/'.$names[0], Crypt::encrypt(serialize([$key => $target->getKey()])));
            file_put_contents($directory.'/'.$names[1], Crypt::encrypt(serialize([$key => $keep->getKey()])));
            file_put_contents($directory.'/'.$names[2], Crypt::encrypt(serialize(['_token' => 'fixture'])));
            $keepBytes = file_get_contents($directory.'/'.$names[1]);
            $result = app(AccountSessionRevoker::class)->revoke($target->getKey());
            $this->assertSame(1, $result['revoked']); $this->assertSame(0, $result['unreadable']);
            $this->assertFileDoesNotExist($directory.'/'.$names[0]);
            $this->assertSame($keepBytes, file_get_contents($directory.'/'.$names[1]));
            $this->assertFileExists($directory.'/'.$names[2]);
        } finally {
            foreach ($names as $name) if (is_file($directory.'/'.$name)) unlink($directory.'/'.$name);
            rmdir($directory);
        }
    }

    public function test_operational_provisioning_cannot_recreate_duplicate_but_testing_fixture_remains(): void
    {
        [$keep, $target] = $this->users();
        // Run on the isolated DB while simulating the local runtime environment.
        // A missing username is enough to exercise provisioning, without deleting staff history.
        DB::table('users')->where('id_user', $target->getKey())->update(['username' => 'qa-retired-'.Str::random(10), 'email' => Str::random(12).'@example.test']);
        DB::table('petugas')->where('id_user', $target->getKey())->update(['kode_petugas' => 'QA-RETIRED-'.Str::random(10)]);
        $originalEnvironment = app()->environment();
        try {
            app()->instance('env', 'local');
            $this->assertSame(0, Artisan::call('yza:seed-local-staff'));
            $this->assertFalse(User::where('username', 'superadmin506')->exists());
            app()->instance('env', 'testing');
            $this->assertSame(0, Artisan::call('yza:seed-local-staff'));
            $this->assertTrue(User::where('username', 'superadmin506')->exists());
        } finally { app()->instance('env', $originalEnvironment); }
    }
}
