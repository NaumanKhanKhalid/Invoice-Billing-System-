<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

class ProfileTest extends TestCase
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

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->get($this->tenantUrl('/profile'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->patch($this->tenantUrl('/profile'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->tenantUrl('/profile'));

        $this->inTenant(function () use ($user) {
            $user->refresh();
            $this->assertSame('Test User', $user->name);
            $this->assertSame('test@example.com', $user->email);
            $this->assertNull($user->email_verified_at);
        });
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->patch($this->tenantUrl('/profile'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->tenantUrl('/profile'));

        $this->inTenant(function () use ($user) {
            $this->assertNotNull($user->refresh()->email_verified_at);
        });
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->delete($this->tenantUrl('/profile'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->tenantUrl());

        $this->assertGuest();
        $this->assertNull($this->inTenant(fn () => $user->fresh()));
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->from($this->tenantUrl('/profile'))
            ->delete($this->tenantUrl('/profile'), [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect($this->tenantUrl('/profile'));

        $this->assertNotNull($this->inTenant(fn () => $user->fresh()));
    }
}
