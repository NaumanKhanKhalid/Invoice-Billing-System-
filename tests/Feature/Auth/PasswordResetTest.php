<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenancy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTenant();
    }

    private function makeUser(): User
    {
        return $this->inTenant(fn () => User::factory()->create());
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get($this->tenantUrl('/forgot-password'));

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = $this->makeUser();

        $this->post($this->tenantUrl('/forgot-password'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = $this->makeUser();

        $this->post($this->tenantUrl('/forgot-password'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get($this->tenantUrl('/reset-password/'.$notification->token));

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = $this->makeUser();

        $this->post($this->tenantUrl('/forgot-password'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post($this->tenantUrl('/reset-password'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }
}
