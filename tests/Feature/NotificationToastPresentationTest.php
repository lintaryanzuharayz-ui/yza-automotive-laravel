<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationToastPresentationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_each_existing_role_renders_one_welcome_and_does_not_replay_on_refresh(): void
    {
        $this->assertSame('yza_automotive_laravel_test', \DB::connection()->getDatabaseName());
        $users = User::with('role')->where('status', 'aktif')->get()->unique(fn ($user) => $user->roleName());
        $this->assertGreaterThanOrEqual(10, $users->count());
        foreach ($users as $user) {
            $this->actingAs($user)->withSession(['yza_toast_welcome_user' => $user->getKey()]);
            $html = view('components.notification-toasts')->render();
            $this->assertStringContainsString('data-welcome="true"', $html, $user->roleName());
            $this->assertStringContainsString('data-account="'.$user->getKey().'"', $html);
            $this->assertStringNotContainsString('data-welcome="true"', view('components.notification-toasts')->render());
        }
    }

    public function test_welcome_for_another_account_is_never_shown(): void
    {
        $user = User::where('status', 'aktif')->firstOrFail();
        $this->actingAs($user)->withSession(['yza_toast_welcome_user' => -1]);
        $this->assertStringContainsString('data-welcome="false"', view('components.notification-toasts')->render());
    }

    public function test_login_event_sets_welcome_for_current_user_and_guest_has_no_toast(): void
    {
        $this->startSession();
        request()->setLaravelSession(app('session.store'));
        $user = User::where('status', 'aktif')->firstOrFail();
        Event::dispatch(new Login('web', $user, false));
        $this->assertSame($user->getAuthIdentifier(), session('yza_toast_welcome_user'));
        auth()->forgetUser();
        $this->assertStringNotContainsString('data-yza-toast-stack', view('components.notification-toasts')->render());
    }
}
