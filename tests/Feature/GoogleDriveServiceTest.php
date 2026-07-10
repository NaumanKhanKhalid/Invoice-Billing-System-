<?php

namespace Tests\Feature;

use App\Services\GoogleDriveService;
use Tests\TestCase;

class GoogleDriveServiceTest extends TestCase
{
    public function test_it_builds_oauth_url_with_expected_scopes(): void
    {
        $service = new GoogleDriveService(
            'test-client-id',
            'test-client-secret',
            'http://127.0.0.1:8000/google/callback'
        );

        $url = $service->buildAuthUrl();

        $this->assertStringContainsString('https://accounts.google.com/o/oauth2/v2/auth', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('redirect_uri=http%3A%2F%2F127.0.0.1%3A8000%2Fgoogle%2Fcallback', $url);
        $this->assertStringContainsString('scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fdrive.file', $url);
    }
}
