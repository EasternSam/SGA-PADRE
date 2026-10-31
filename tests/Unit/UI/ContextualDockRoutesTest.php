<?php

namespace Tests\Unit\UI;

use Tests\TestCase;

class ContextualDockRoutesTest extends TestCase
{
    public function test_contextual_dock_does_not_contain_invalid_or_405_routes(): void
    {
        $basePath = dirname(__DIR__, 3);
        $layout = file_get_contents($basePath . '/resources/js/Layouts/AuthenticatedLayout.jsx');

        $invalidVisits = [
            "router.visit('/admin/students/create')",
            "router.visit('/admin/teachers/create')",
            "router.visit('/admin/finance')",
            "router.visit('/admin/accounting')",
            "router.visit('/admin/hr')",
            "router.visit('/admin/payroll')",
            "router.visit('/teacher/sections')",
        ];

        foreach ($invalidVisits as $invalidVisit) {
            $this->assertStringNotContainsString(
                $invalidVisit,
                $layout,
                "AuthenticatedLayout contextual dock must not use invalid route {$invalidVisit}."
            );
        }
    }

    public function test_student_and_teacher_pages_handle_create_modal_triggers_from_dock(): void
    {
        $basePath = dirname(__DIR__, 3);
        $studentsIndex = file_get_contents($basePath . '/resources/js/Pages/Students/Index.jsx');
        $teachersIndex = file_get_contents($basePath . '/resources/js/Pages/Teachers/Index.jsx');
        $adminRoutes = file_get_contents($basePath . '/routes/admin.php');

        $this->assertStringContainsString('sga:open-create-student', $studentsIndex);
        $this->assertStringContainsString('sga:open-create-teacher', $teachersIndex);
        $this->assertStringContainsString("Route::get('/students/create'", $adminRoutes);
        $this->assertStringContainsString("Route::get('/teachers/create'", $adminRoutes);
    }
}
