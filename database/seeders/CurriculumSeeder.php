<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Preparar datos auxiliares (Profesores y Aulas)
        // Intentamos buscar profesores, si no hay, buscamos cualquier usuario para evitar fallos.
        $teachers = User::role('Profesor')->get();
        if ($teachers->isEmpty()) {
            $teachers = User::limit(5)->get();
            $this->command->warn('No se encontraron usuarios con rol "Profesor". Se usarán usuarios aleatorios para las secciones.');
        }
        
        $classrooms = Classroom::all();
        if ($classrooms->isEmpty()) {
            $this->command->warn('No se encontraron aulas. Las secciones se crearán sin aula asignada.');
        }

        // 1. Crear o Buscar la Carrera (CORREGIDO: firstOrCreate para evitar duplicados)
        // CAMBIO IMPORTANTE: Usamos un código nuevo para evitar conflictos con datos existentes
        $courseCode = 'IDS-2026-B'; 
        
        $course = Course::firstOrCreate(
            ['code' => $courseCode], // Buscar por este código nuevo
            [
                'name' => 'Ingeniería en Desarrollo de Software (Plan B)',
                'program_type' => 'degree', 
                'description' => 'Carrera enfocada en el desarrollo, arquitectura y gestión de soluciones de software modernas.',
                'status' => 'Activo',
                'duration_periods' => 7,
                'total_credits' => 0,
                'registration_fee' => 5000.00,
                'monthly_fee' => 4500.00,
            ]
        );

        // 2. Definir el Pensum con Prerrequisitos
        // Estructura: ['code', 'name', 'credits', 'prereqs' => ['CODE1', 'CODE2']]
        $curriculum = [
            1 => [
                ['code' => 'ESP-101-B', 'name' => 'Lengua Española I', 'credits' => 3, 'prereqs' => []],
                ['code' => 'MAT-101-B', 'name' => 'Matemática Básica', 'credits' => 4, 'prereqs' => []],
                ['code' => 'INF-100-B', 'name' => 'Introducción a la Informática', 'credits' => 3, 'prereqs' => []],
                ['code' => 'SOC-101-B', 'name' => 'Orientación Institucional', 'credits' => 2, 'prereqs' => []],
                ['code' => 'ING-101-B', 'name' => 'Inglés I', 'credits' => 3, 'prereqs' => []],
            ],
            2 => [
                ['code' => 'ESP-102-B', 'name' => 'Lengua Española II', 'credits' => 3, 'prereqs' => ['ESP-101-B']],
                ['code' => 'MAT-102-B', 'name' => 'Cálculo Diferencial', 'credits' => 4, 'prereqs' => ['MAT-101-B']],
                ['code' => 'INF-101-B', 'name' => 'Algoritmos y Programación', 'credits' => 4, 'prereqs' => ['INF-100-B', 'MAT-101-B']],
                ['code' => 'FIS-101-B', 'name' => 'Física General', 'credits' => 3, 'prereqs' => ['MAT-101-B']],
                ['code' => 'ING-102-B', 'name' => 'Inglés II', 'credits' => 3, 'prereqs' => ['ING-101-B']],
            ],
            3 => [
                ['code' => 'MAT-201-B', 'name' => 'Cálculo Integral', 'credits' => 4, 'prereqs' => ['MAT-102-B']],
                ['code' => 'INF-102-B', 'name' => 'Programación Orientada a Objetos', 'credits' => 4, 'prereqs' => ['INF-101-B']],
                ['code' => 'INF-103-B', 'name' => 'Base de Datos I', 'credits' => 4, 'prereqs' => ['INF-101-B']],
                ['code' => 'FIS-102-B', 'name' => 'Física Eléctrica', 'credits' => 3, 'prereqs' => ['FIS-101-B', 'MAT-102-B']],
                ['code' => 'ING-103-B', 'name' => 'Inglés Técnico', 'credits' => 3, 'prereqs' => ['ING-102-B']],
            ],
            4 => [
                ['code' => 'INF-201-B', 'name' => 'Estructura de Datos', 'credits' => 4, 'prereqs' => ['INF-102-B']],
                ['code' => 'INF-202-B', 'name' => 'Base de Datos II', 'credits' => 4, 'prereqs' => ['INF-103-B']],
                ['code' => 'INF-203-B', 'name' => 'Sistemas Operativos', 'credits' => 3, 'prereqs' => ['INF-100-B']],
                ['code' => 'EST-201-B', 'name' => 'Estadística y Probabilidad', 'credits' => 3, 'prereqs' => ['MAT-101-B']],
                ['code' => 'ADM-201-B', 'name' => 'Administración de Proyectos', 'credits' => 3, 'prereqs' => ['INF-100-B']],
            ],
            5 => [
                ['code' => 'INF-301-B', 'name' => 'Desarrollo Web I', 'credits' => 4, 'prereqs' => ['INF-102-B', 'INF-103-B']],
                ['code' => 'INF-302-B', 'name' => 'Análisis y Diseño de Sistemas', 'credits' => 4, 'prereqs' => ['INF-103-B', 'ADM-201-B']],
                ['code' => 'INF-303-B', 'name' => 'Redes de Computadoras I', 'credits' => 3, 'prereqs' => ['INF-203-B']],
                ['code' => 'INF-304-B', 'name' => 'Metodología de la Investigación', 'credits' => 3, 'prereqs' => ['ESP-102-B']],
                ['code' => 'ELE-001-B', 'name' => 'Electiva Profesional I', 'credits' => 3, 'is_elective' => true, 'prereqs' => []],
            ],
            6 => [
                ['code' => 'INF-305-B', 'name' => 'Desarrollo Web II', 'credits' => 4, 'prereqs' => ['INF-301-B']],
                ['code' => 'INF-306-B', 'name' => 'Ingeniería de Software', 'credits' => 4, 'prereqs' => ['INF-302-B']],
                ['code' => 'INF-307-B', 'name' => 'Redes de Computadoras II', 'credits' => 3, 'prereqs' => ['INF-303-B']],
                ['code' => 'INF-308-B', 'name' => 'Seguridad Informática', 'credits' => 3, 'prereqs' => ['INF-303-B']],
                ['code' => 'ELE-002-B', 'name' => 'Electiva Profesional II', 'credits' => 3, 'is_elective' => true, 'prereqs' => []],
            ],
            7 => [
                ['code' => 'INF-401-B', 'name' => 'Inteligencia Artificial', 'credits' => 3, 'prereqs' => ['INF-201-B', 'EST-201-B']],
                ['code' => 'INF-402-B', 'name' => 'Desarrollo de Apps Móviles', 'credits' => 4, 'prereqs' => ['INF-102-B']],
                ['code' => 'INF-403-B', 'name' => 'Ética Profesional', 'credits' => 2, 'prereqs' => ['SOC-101-B']],
                ['code' => 'INF-490-B', 'name' => 'Proyecto Final de Grado', 'credits' => 6, 'prereqs' => ['INF-304-B', 'INF-306-B']],
            ],
        ];

        $totalCredits = 0;
        $hasOrderColumn = Schema::hasColumn('modules', 'order');
        
        // Mapa para guardar las instancias de módulos creados y poder asignar prerrequisitos luego
        // Clave: Código (ej: MAT-101), Valor: Modelo Module
        $createdModulesMap = [];

        foreach ($curriculum as $period => $modulesData) {
            $order = 1;
            foreach ($modulesData as $data) {
                // A. Crear o Buscar Módulo (CORREGIDO: firstOrCreate)
                $moduleData = [
                    'course_id' => $course->id,
                    'name' => $data['name'],
                    'credits' => $data['credits'],
                    'period_number' => $period,
                    'is_elective' => $data['is_elective'] ?? false,
                    'status' => 'Activo',
                    'description' => 'Materia del cuatrimestre ' . $period,
                ];

                if ($hasOrderColumn) {
                    $moduleData['order'] = $order++;
                }

                $module = Module::firstOrCreate(
                    ['code' => $data['code'], 'course_id' => $course->id], // Clave de búsqueda (Código + ID Curso)
                    $moduleData
                );

                $createdModulesMap[$data['code']] = $module; // Guardar referencia para prerrequisitos
                $totalCredits += $data['credits'];

                // B. Crear Secciones (Horarios) - Opcional, crea 1 o 2 por materia
                // Solo creamos si no existen ya, para no duplicar infinitamente al correr el seeder de nuevo
                if ($teachers->isNotEmpty() && $module->schedules()->count() == 0) {
                    $numSections = rand(1, 2); // 1 o 2 secciones por materia
                    
                    for ($i = 1; $i <= $numSections; $i++) {
                        $teacher = $teachers->random();
                        $classroom = $classrooms->isNotEmpty() ? $classrooms->random() : null;
                        
                        // Generar horario aleatorio
                        $daysOptions = [['Lunes', 'Miércoles'], ['Martes', 'Jueves'], ['Viernes'], ['Sábado']];
                        $selectedDays = $daysOptions[array_rand($daysOptions)];
                        
                        $startHour = rand(8, 19);
                        $startTime = sprintf('%02d:00', $startHour);
                        $endTime = sprintf('%02d:00', $startHour + 2); // Clases de 2 horas

                        CourseSchedule::create([
                            'module_id' => $module->id,
                            'teacher_id' => $teacher->id,
                            'classroom_id' => $classroom ? $classroom->id : null,
                            'section_name' => 'Sec-0' . $i,
                            'days_of_week' => $selectedDays, // Array directo gracias al cast en el modelo
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'modality' => 'Presencial',
                            'start_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                            'end_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                            'status' => 'Activo',
                        ]);
                    }
                }
            }
        }

        // 3. Asignar Prerrequisitos (Segunda pasada)
        foreach ($curriculum as $period => $modulesData) {
            foreach ($modulesData as $data) {
                if (!empty($data['prereqs'])) {
                    $currentModule = $createdModulesMap[$data['code']];
                    $prerequisiteIds = [];

                    foreach ($data['prereqs'] as $prereqCode) {
                        if (isset($createdModulesMap[$prereqCode])) {
                            $prerequisiteIds[] = $createdModulesMap[$prereqCode]->id;
                        }
                    }

                    // Sincronizar relación many-to-many
                    if (!empty($prerequisiteIds) && method_exists($currentModule, 'prerequisites')) {
                        $currentModule->prerequisites()->sync($prerequisiteIds);
                    }
                }
            }
        }

        // 4. Actualizar créditos totales (Esto podría sobreescribir el valor si cambia el pensum)
        $course->update(['total_credits' => $totalCredits]);

        $this->command->info("¡Pensum completo procesado! Carrera: {$course->name} con {$totalCredits} créditos.");
        $this->command->info("Secciones y prerrequisitos asignados/verificados correctamente.");
    }
}