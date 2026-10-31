<?php

namespace Tests\Unit\Student;

use App\Services\Student\StudentDashboardService;
use PHPUnit\Framework\TestCase;

class StudentDashboardServiceTest extends TestCase
{
    private StudentDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StudentDashboardService();
    }

    public function test_format_days_formats_and_orders_weekdays_chronologically(): void
    {
        $this->assertSame('Miércoles, Sábado', $this->service->formatDays(['Sábado', 'Miércoles']));
        $this->assertSame('Lunes, Miércoles, Viernes', $this->service->formatDays(['Viernes', 'Lunes', 'Miércoles']));
        $this->assertSame('Martes, Jueves', $this->service->formatDays('["Jueves", "Martes"]'));
        $this->assertSame('Por coordinar', $this->service->formatDays([]));
        $this->assertSame('Por coordinar', $this->service->formatDays(null));
    }

    public function test_calculate_attendance_stats_with_records(): void
    {
        $attendances = [
            ['status' => 'Presente'],
            ['status' => 'Presente'],
            ['status' => 'Presente'],
            ['status' => 'Presente'],
            ['status' => 'Presente'],
            ['status' => 'Presente'],
            ['status' => 'Tardanza'],
            ['status' => 'Tardanza'],
            ['status' => 'Ausente'],
            ['status' => 'Ausente'],
        ];

        $stats = $this->service->calculateAttendanceStats($attendances);

        $this->assertSame(10, $stats['total']);
        $this->assertSame(6, $stats['present']);
        $this->assertSame(2, $stats['late']);
        $this->assertSame(2, $stats['absent']);
        // 6 presentes + 2 tardanzas (50% valor) = 7/10 = 70%
        $this->assertSame(70, $stats['percentage']);
        $this->assertSame('Regular', $stats['rate_badge']);
    }

    public function test_calculate_attendance_stats_empty(): void
    {
        $stats = $this->service->calculateAttendanceStats([]);

        $this->assertSame(0, $stats['total']);
        $this->assertSame(0, $stats['present']);
        $this->assertSame(0, $stats['late']);
        $this->assertSame(0, $stats['absent']);
        $this->assertSame(100, $stats['percentage']);
        $this->assertSame('Sin registros', $stats['rate_badge']);
    }

    public function test_build_weekly_schedule_buckets_enrollments_correctly(): void
    {
        $enrollments = [
            [
                'id' => 1,
                'subject_name' => 'Diseño Gráfico Avanzado',
                'subject_code' => 'DG-101',
                'teacher' => 'Prof. Juan',
                'classroom' => 'Lab B-108',
                'days_of_week' => ['Miércoles', 'Sábado'],
                'start_time' => '18:00',
                'end_time' => '20:00',
            ],
            [
                'id' => 2,
                'subject_name' => 'Programación Web',
                'subject_code' => 'TI-201',
                'teacher' => 'Prof. Ana',
                'classroom' => 'Aula 204',
                'days_of_week' => ['Lunes', 'Miércoles'],
                'start_time' => '08:00',
                'end_time' => '10:00',
            ],
        ];

        $schedule = $this->service->buildWeeklySchedule($enrollments);

        $this->assertArrayHasKey('Lunes', $schedule);
        $this->assertArrayHasKey('Miércoles', $schedule);
        $this->assertArrayHasKey('Sábado', $schedule);

        // Lunes has 1 class
        $this->assertCount(1, $schedule['Lunes']);
        $this->assertSame('TI-201', $schedule['Lunes'][0]['subject_code']);

        // Miércoles has 2 classes, ordered by start_time: 08:00 then 18:00
        $this->assertCount(2, $schedule['Miércoles']);
        $this->assertSame('TI-201', $schedule['Miércoles'][0]['subject_code']);
        $this->assertSame('DG-101', $schedule['Miércoles'][1]['subject_code']);

        // Sábado has 1 class
        $this->assertCount(1, $schedule['Sábado']);
        $this->assertSame('DG-101', $schedule['Sábado'][0]['subject_code']);
    }

    public function test_calculate_academic_progress_computes_percentages_cleanly(): void
    {
        $progress = $this->service->computeProgress(
            totalCredits: 60,
            approvedCredits: 30,
            inProgressCredits: 12
        );

        $this->assertSame(60, $progress['total_credits']);
        $this->assertSame(30, $progress['approved_credits']);
        $this->assertSame(12, $progress['in_progress_credits']);
        $this->assertSame(50, $progress['percentage']);
        $this->assertSame(18, $progress['pending_credits']);
    }
}
