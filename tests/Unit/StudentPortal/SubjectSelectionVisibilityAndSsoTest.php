<?php

namespace Tests\Unit\StudentPortal;

use Tests\TestCase;

class SubjectSelectionVisibilityAndSsoTest extends TestCase
{
    public function test_student_dashboard_does_not_contain_acceso_directo_sso(): void
    {
        $basePath = dirname(__DIR__, 3);
        $dashboard = file_get_contents($basePath . '/resources/js/Pages/StudentPortal/Dashboard.jsx');

        $this->assertStringNotContainsString('Acceso directo SSO', $dashboard);
    }

    public function test_admissions_open_controls_subject_selection_visibility_and_access(): void
    {
        $basePath = dirname(__DIR__, 3);
        $inertiaMiddleware = file_get_contents($basePath . '/app/Http/Middleware/HandleInertiaRequests.php');
        $admissionController = file_get_contents($basePath . '/app/Http/Controllers/AdmissionController.php');
        $admissionsPipeline = file_get_contents($basePath . '/resources/js/Pages/Admissions/Pipeline.jsx');
        $studentController = file_get_contents($basePath . '/app/Http/Controllers/StudentPortalController.php');
        $layout = file_get_contents($basePath . '/resources/js/Layouts/AuthenticatedLayout.jsx');
        $studentDashboard = file_get_contents($basePath . '/resources/js/Pages/StudentPortal/Dashboard.jsx');
        $adminRoutes = file_get_contents($basePath . '/routes/admin.php');

        $this->assertStringContainsString("'admissions_open'", $inertiaMiddleware);
        $this->assertStringContainsString('toggleAdmissionsMode', $admissionController);
        $this->assertStringContainsString('/admissions/toggle-mode', $adminRoutes);
        $this->assertStringContainsString('Admisiones Abiertas', $admissionsPipeline);
        $this->assertStringContainsString("Setting::get('admissions_open'", $studentController);
        $this->assertStringContainsString('admissionsOpen', $layout);
        $this->assertStringContainsString('admissionsOpen', $studentDashboard);
    }
}
