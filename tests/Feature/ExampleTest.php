<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Note: the tenant routes file also registers a '/' route (loaded after
     * web.php), so the central root is shadowed and returns 404. The admin
     * login page is the canonical always-available central endpoint.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }
}
