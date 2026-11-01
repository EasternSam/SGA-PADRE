<?php

namespace Tests\Unit\Notifications;

use PHPUnit\Framework\TestCase;

class NotificationUrlResolutionTest extends TestCase
{
    public function test_handle_inertia_requests_normalizes_notification_urls_and_web_routes_define_portal_fallback(): void
    {
        $basePath = dirname(__DIR__, 3);
        $middleware = file_get_contents($basePath . '/app/Http/Middleware/HandleInertiaRequests.php');
        $webRoutes = file_get_contents($basePath . '/routes/web.php');

        $this->assertStringContainsString('normalizeNotificationUrl', $middleware);
        $this->assertStringContainsString("'/portal'", $middleware);
        $this->assertStringContainsString("'/admin/students/profile/'", $middleware);
        $this->assertStringContainsString("Route::get('/portal'", $webRoutes);
    }
}
