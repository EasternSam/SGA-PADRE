<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Facades\Schema;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear la Carrera (Course)
        // Usamos 'degree' en program_type para que aparezca en el Curriculum
        $course = Course::create([
            'name' => 'Ingeniería en Desarrollo de Software',
            'code' => 'IDS-2026',
            'program_type' => 'degree', 
            'description' => 'Carrera enfocada en el desarrollo, arquitectura y gestión de soluciones de software modernas.',
            'status' => 'Activo',
            'duration_periods' => 7,
            'total_credits' => 0, // Se actualizará al final
            
            // Valores por defecto para evitar errores si tienes campos de pagos requeridos
            'registration_fee' => 5000.00,
            'monthly_fee' => 4500.00,
        ]);

        // 2. Definir el Pensum (7 Cuatrimestres)
        $curriculum = [
            1 => [
                ['code' => 'ESP-101', 'name' => 'Lengua Española I', 'credits' => 3],
                ['code' => 'MAT-101', 'name' => 'Matemática Básica', 'credits' => 4],
                ['code' => 'INF-100', 'name' => 'Introducción a la Informática', 'credits' => 3],
                ['code' => 'SOC-101', 'name' => 'Orientación Institucional', 'credits' => 2],
                ['code' => 'ING-101', 'name' => 'Inglés I', 'credits' => 3],
            ],
            2 => [
                ['code' => 'ESP-102', 'name' => 'Lengua Española II', 'credits' => 3],
                ['code' => 'MAT-102', 'name' => 'Cálculo Diferencial', 'credits' => 4],
                ['code' => 'INF-101', 'name' => 'Algoritmos y Programación', 'credits' => 4],
                ['code' => 'FIS-101', 'name' => 'Física General', 'credits' => 3],
                ['code' => 'ING-102', 'name' => 'Inglés II', 'credits' => 3],
            ],
            3 => [
                ['code' => 'MAT-201', 'name' => 'Cálculo Integral', 'credits' => 4],
                ['code' => 'INF-102', 'name' => 'Programación Orientada a Objetos', 'credits' => 4],
                ['code' => 'INF-103', 'name' => 'Base de Datos I', 'credits' => 4],
                ['code' => 'FIS-102', 'name' => 'Física Eléctrica', 'credits' => 3],
                ['code' => 'ING-103', 'name' => 'Inglés Técnico', 'credits' => 3],
            ],
            4 => [
                ['code' => 'INF-201', 'name' => 'Estructura de Datos', 'credits' => 4],
                ['code' => 'INF-202', 'name' => 'Base de Datos II', 'credits' => 4],
                ['code' => 'INF-203', 'name' => 'Sistemas Operativos', 'credits' => 3],
                ['code' => 'EST-201', 'name' => 'Estadística y Probabilidad', 'credits' => 3],
                ['code' => 'ADM-201', 'name' => 'Administración de Proyectos', 'credits' => 3],
            ],
            5 => [
                ['code' => 'INF-301', 'name' => 'Desarrollo Web I', 'credits' => 4],
                ['code' => 'INF-302', 'name' => 'Análisis y Diseño de Sistemas', 'credits' => 4],
                ['code' => 'INF-303', 'name' => 'Redes de Computadoras I', 'credits' => 3],
                ['code' => 'INF-304', 'name' => 'Metodología de la Investigación', 'credits' => 3],
                ['code' => 'ELE-001', 'name' => 'Electiva Profesional I', 'credits' => 3, 'is_elective' => true],
            ],
            6 => [
                ['code' => 'INF-305', 'name' => 'Desarrollo Web II', 'credits' => 4],
                ['code' => 'INF-306', 'name' => 'Ingeniería de Software', 'credits' => 4],
                ['code' => 'INF-307', 'name' => 'Redes de Computadoras II', 'credits' => 3],
                ['code' => 'INF-308', 'name' => 'Seguridad Informática', 'credits' => 3],
                ['code' => 'ELE-002', 'name' => 'Electiva Profesional II', 'credits' => 3, 'is_elective' => true],
            ],
            7 => [
                ['code' => 'INF-401', 'name' => 'Inteligencia Artificial', 'credits' => 3],
                ['code' => 'INF-402', 'name' => 'Desarrollo de Apps Móviles', 'credits' => 4],
                ['code' => 'INF-403', 'name' => 'Ética Profesional', 'credits' => 2],
                ['code' => 'INF-490', 'name' => 'Proyecto Final de Grado', 'credits' => 6],
            ],
        ];

        $totalCredits = 0;
        $hasOrderColumn = Schema::hasColumn('modules', 'order');

        foreach ($curriculum as $period => $modules) {
            $order = 1;
            foreach ($modules as $data) {
                $moduleData = [
                    'course_id' => $course->id,
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'credits' => $data['credits'],
                    'period_number' => $period,
                    'is_elective' => $data['is_elective'] ?? false,
                    'status' => 'Activo',
                    'description' => 'Materia correspondiente al cuatrimestre ' . $period,
                ];

                if ($hasOrderColumn) {
                    $moduleData['order'] = $order++;
                }

                Module::create($moduleData);
                $totalCredits += $data['credits'];
            }
        }

        // Actualizar el total de créditos en la carrera
        $course->update(['total_credits' => $totalCredits]);

        $this->command->info("¡Pensum creado! Carrera: {$course->name} con {$totalCredits} créditos.");
    }
}