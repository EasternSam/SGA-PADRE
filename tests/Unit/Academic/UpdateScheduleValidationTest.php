<?php

namespace Tests\Unit\Academic;

use Tests\TestCase;

class UpdateScheduleValidationTest extends TestCase
{
    public function test_update_schedule_accepts_programada_status_and_optional_dates(): void
    {
        $basePath = dirname(__DIR__, 3);
        $controller = file_get_contents($basePath . '/app/Http/Controllers/AcademicCourseController.php');
        $coursesIndex = file_get_contents($basePath . '/resources/js/Pages/Academic/Courses/Index.jsx');

        $updatePos = strpos($controller, 'public function updateSchedule');
        $destroyPos = strpos($controller, 'public function destroySchedule');

        $this->assertNotFalse($updatePos);
        $this->assertNotFalse($destroyPos);

        $updateBlock = substr($controller, $updatePos, $destroyPos - $updatePos);

        $this->assertStringNotContainsString("'status' => 'nullable|string|in:Activo,Inactivo'", $updateBlock);
        $this->assertStringContainsString("'start_date' => 'nullable|date'", $updateBlock);
        $this->assertStringContainsString('scheduleForm.errors', $coursesIndex);
    }
}
