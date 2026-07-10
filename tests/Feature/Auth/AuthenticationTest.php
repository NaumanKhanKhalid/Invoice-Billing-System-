<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenancy;

    protected function setUp(): void
    {
        parent::setUp();
        // Auth routes only exist on tenant domains in this app
        $this->createTenant();
    }

    private function makeUser(): User
    {
        return $this->inTenant(fn () => User::factory()->create());
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get($this->tenantUrl('/login'));

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = $this->makeUser();

        $response = $this->post($this->tenantUrl('/login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = $this->makeUser();

        $this->post($this->tenantUrl('/login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post($this->tenantUrl('/logout'));

        $this->assertGuest();
        $response->assertRedirect($this->tenantUrl());
    }
}
