<?php

namespace Tests\Unit\Dashboard;

use App\Services\Dashboard\Support\ActivityHumanizer;
use App\Services\Dashboard\Support\AgingBucket;
use App\Services\Dashboard\Support\Weekday;
use PHPUnit\Framework\TestCase;

/**
 * Lógica pura que usan los providers del dashboard.
 */
class DashboardSupportTest extends TestCase
{
    public function test_weekday_normalizes_accents_case_and_escaped_variants(): void
    {
        $this->assertSame('Miércoles', Weekday::normalize('Miércoles'));
        $this->assertSame('Miércoles', Weekday::normalize('miercoles'));
        $this->assertSame('Sábado', Weekday::normalize(' SABADO '));
        $this->assertSame('Lunes', Weekday::normalize('Lunes'));
        $this->assertNull(Weekday::normalize('Funday'));
        $this->assertNull(Weekday::normalize(''));
    }

    public function test_weekday_decodes_the_days_of_week_column(): void
    {
        $this->assertSame(['Viernes', 'Miércoles'], Weekday::decode('["Viernes","Mi\u00e9rcoles"]'));
        $this->assertSame(['Lunes'], Weekday::decode(['lunes', 'Lunes', 'xx']));
        $this->assertSame([], Weekday::decode('not json'));
        $this->assertSame([], Weekday::decode(null));
    }

    public function test_weekday_maps_carbon_day_numbers(): void
    {
        $this->assertSame('Domingo', Weekday::fromDayOfWeek(0));
        $this->assertSame('Miércoles', Weekday::fromDayOfWeek(3));
        $this->assertSame('Sábado', Weekday::fromDayOfWeek(6));
    }

    public function test_activity_humanizer_rewrites_known_log_formats(): void
    {
        $this->assertSame(
            ['text' => 'Pago registrado: RD$ 2,500.00 (Pagado)', 'kind' => 'payment'],
            ActivityHumanizer::describe('Creó registro en Payment: Monto RD$ 2,500.00 (Estado: Pagado)', 'Payment Creó')
        );
        $this->assertSame(
            ['text' => 'Nuevo estudiante: Sofia Torres', 'kind' => 'student'],
            ActivityHumanizer::describe('Creó registro en Student: Sofia Torres (Matrícula: )', 'Student Creó')
        );
        $this->assertSame(
            ['text' => 'Estudiante actualizado: Sofia Rodriguez', 'kind' => 'student'],
            ActivityHumanizer::describe('Actualizó registro en Student: Sofia Rodriguez (Matrícula: )', 'Student Actualizó')
        );
        $this->assertSame(
            ['text' => 'Se inscribió al estudiante Sofia Rodriguez en Curso de Contabilidad Base 44 (C-060) - Sección 1.', 'kind' => 'enrollment'],
            ActivityHumanizer::describe('Se inscribió al estudiante Sofia Rodriguez en Curso de Contabilidad Base 44 (C-060) - Sección 1.', 'Inscripción')
        );
    }

    public function test_activity_humanizer_falls_back_to_the_raw_text(): void
    {
        $this->assertSame(
            ['text' => 'Creó registro en CourseSchedule: ID #99', 'kind' => 'schedule'],
            ActivityHumanizer::describe('Creó registro en CourseSchedule: ID #99', 'CourseSchedule Creó')
        );
        $this->assertSame(['text' => 'Actividad del sistema', 'kind' => 'general'], ActivityHumanizer::describe(null, null));
    }

    public function test_aging_bucket_classifies_days_overdue(): void
    {
        $this->assertSame('current', AgingBucket::for(null));
        $this->assertSame('current', AgingBucket::for(0));
        $this->assertSame('current', AgingBucket::for(-5));
        $this->assertSame('1_30', AgingBucket::for(1));
        $this->assertSame('1_30', AgingBucket::for(30));
        $this->assertSame('31_60', AgingBucket::for(31));
        $this->assertSame('61_90', AgingBucket::for(90));
        $this->assertSame('90_plus', AgingBucket::for(91));
        $this->assertSame(['current', '1_30', '31_60', '61_90', '90_plus'], array_keys(AgingBucket::LABELS));
    }
}
