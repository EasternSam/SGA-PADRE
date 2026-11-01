<?php

namespace Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;

class CsrfPageExpiredHandlingTest extends TestCase
{
    public function test_bootstrap_app_handles_419_page_expired_gracefully_in_spanish(): void
    {
        $basePath = dirname(__DIR__, 3);
        $bootstrap = file_get_contents($basePath . '/bootstrap/app.php');
        $loginView = file_get_contents($basePath . '/resources/js/Pages/Auth/Login.jsx');

        $this->assertStringContainsString('$exceptions->respond(', $bootstrap);
        $this->assertStringContainsString('getStatusCode() === 419', $bootstrap);
        $this->assertStringContainsString('La sesión de seguridad se actualizó automáticamente', $bootstrap);
        $this->assertStringContainsString('visibilitychange', $loginView);
    }
}
