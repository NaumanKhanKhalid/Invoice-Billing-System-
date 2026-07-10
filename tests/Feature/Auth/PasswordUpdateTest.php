<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
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

    public function test_password_can_be_updated(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->from($this->tenantUrl('/profile'))
            ->put($this->tenantUrl('/password'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->tenantUrl('/profile'));

        $fresh = $this->inTenant(fn () => $user->refresh());
        $this->assertTrue(Hash::check('new-password', $fresh->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->from($this->tenantUrl('/profile'))
            ->put($this->tenantUrl('/password'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect($this->tenantUrl('/profile'));
    }
}
