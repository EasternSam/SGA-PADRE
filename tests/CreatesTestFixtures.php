<?php

namespace Tests;

use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Module;

/**
 * Trait para crear fixtures de base de datos con todas las dependencias
 * requeridas por las constraints NOT NULL de MySQL.
 */
trait CreatesTestFixtures
{
    protected function createCourseSchedule(array $overrides = []): CourseSchedule
    {
        $course = Course::first() ?? Course::create([
            'name' => 'Test Course',
            'type' => 'Curso',
            'status' => 'Activo',
        ]);

        $module = Module::first() ?? Module::create([
            'course_id' => $course->id,
            'name' => 'Módulo Test',
            'status' => 'Activo',
        ]);

        return CourseSchedule::create(array_merge([
            'module_id' => $module->id,
            'status' => 'Activa',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(4)->format('Y-m-d'),
        ], $overrides));
    }
}
