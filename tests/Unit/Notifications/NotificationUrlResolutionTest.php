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
        $this->assertStringContainsString("Route::get('/portal'", $webRoutes);
    }

    public function test_web_routes_do_not_shadow_real_admin_student_profile_route(): void
    {
        $basePath = dirname(__DIR__, 3);
        $webRoutes = file_get_contents($basePath . '/routes/web.php');
        $adminRoutes = file_get_contents($basePath . '/routes/admin.php');
        $middleware = file_get_contents($basePath . '/app/Http/Middleware/HandleInertiaRequests.php');

        // web.php se carga antes que admin.php: no debe declarar /admin/students/profile/*
        $this->assertStringNotContainsString("/admin/students/profile/{", $webRoutes);
        $this->assertStringContainsString("Route::get('/students/profile/{student}'", $adminRoutes);

        // Nunca reescribir hacia el formato inexistente /admin/students/{id}/profile
        $this->assertStringNotContainsString('return "/admin/students/{$studentId}/profile"', $middleware);
        $this->assertStringContainsString('return "/admin/students/profile/{$m[1]}"', $middleware);
    }
}
