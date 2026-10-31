<?php

namespace Tests\Unit\Reports;

use App\Models\Attendance;
use ReflectionClass;
use Tests\TestCase;

class ReportCenterAttendanceRelationTest extends TestCase
{
    public function test_attendance_model_defines_course_schedule_relationship(): void
    {
        $reflection = new ReflectionClass(Attendance::class);

        $this->assertTrue(
            $reflection->hasMethod('courseSchedule'),
            'Attendance model must define courseSchedule() relationship used by ReportCenterController.'
        );
    }
}
