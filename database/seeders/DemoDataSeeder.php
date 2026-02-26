<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Building;
use App\Models\Classroom;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstNames = ['Juan', 'Pedro', 'Maria', 'Laura', 'Luis', 'Ana', 'Carlos', 'Jose', 'Marta', 'Lucia', 'Miguel', 'Sofia', 'Elena', 'David', 'Andres', 'Jorge', 'Silvia'];
        $lastNames = ['Perez', 'Gomez', 'Rodriguez', 'Fernandez', 'Lopez', 'Martinez', 'Sanchez', 'Diaz', 'Torres', 'Ramirez', 'Castillo', 'Brito', 'Peña'];

        $teacherRole = Role::where('name', 'Profesor')->first();
        $studentRole = Role::where('name', 'Estudiante')->first();

        if (!$teacherRole || !$studentRole) {
            $this->command->error('Los roles "Profesor" o "Estudiante" no se encontraron (Ejecuta RolesAndPermissionsSeeder).');
            return;
        }

        // --- 1. Crear Profesores ---
        $teachers = collect();
        $teacherNames = ['Ana Gómez', 'Carlos Ruiz', 'María López', 'Juan Torres', 'Lucía Fernández', 'Pedro Sánchez', 'Elena Vidal', 'Miguel Costa', 'Sofía Romero', 'David Martín'];
        foreach ($teacherNames as $index => $name) {
            $teachers->push(
                User::firstOrCreate(['email' => 'profesor' . ($index + 1) . '@sga.com'], ['name' => $name, 'password' => Hash::make('password')])->assignRole($teacherRole)
            );
        }

        // --- 2. Crear Edificios y Aulas ---
        $buildingA = Building::firstOrCreate(['name' => 'Edificio Principal (Sede)']);
        $buildingB = Building::firstOrCreate(['name' => 'Campus Norte (Anexo)']);
        
        $classrooms = collect();
        for ($i = 1; $i <= 10; $i++) {
            $classrooms->push(Classroom::firstOrCreate([
                'building_id' => $i <= 5 ? $buildingA->id : $buildingB->id,
                'name' => ($i <= 5 ? 'Aula A-' : 'Lab B-') . (100 + $i),
                'capacity' => rand(25, 45),
                'type' => $i <= 5 ? 'Aula' : 'Laboratorio',
                'is_active' => true,
            ]));
        }

        // --- 3. Crear Cursos, Módulos y Secciones ---
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $horasInicio = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'];
        $bsWords = ['Plataforma Interactiva', 'Gestión Avanzada', 'Sistemas Digitales', 'Desarrollo Web', 'Marketing Estratégico', 'Diseño Gráfico', 'Arquitectura de Datos', 'Inteligencia Artificial', 'Finanzas Modernas', 'Contabilidad Base'];

        $totalCourses = 70;
        $this->command->getOutput()->progressStart($totalCourses);
        $courses = collect();

        for ($c = 1; $c <= $totalCourses; $c++) {
            $courseCode = 'C-' . str_pad($c, 3, '0', STR_PAD_LEFT);
            $courseBaseName = $bsWords[array_rand($bsWords)] . ' ' . rand(1, 99); 
            $courseName = 'Curso de ' . $courseBaseName . ' (' . $courseCode . ')'; 

            // Diferenciar entre Carreras y Técnicos
            $isDegree = rand(1, 100) > 70; // 30% son carreras universitarias

            $course = Course::updateOrCreate(
                ['code' => $courseCode],
                [
                    'name' => $courseName,
                    'program_type' => $isDegree ? 'degree' : 'technical',
                    'degree_title' => $isDegree ? 'Licenciatura en ' . $courseBaseName : null,
                    'total_credits' => $isDegree ? rand(120, 160) : 0,
                    'duration_periods' => $isDegree ? rand(8, 12) : null,
                    'is_sequential' => true,
                    'status' => 'Activo',
                ]
            );
            $courses->push($course);

            $numModules = rand(3, 5);
            for ($m = 1; $m <= $numModules; $m++) {
                $module = Module::firstOrCreate(
                    ['course_id' => $course->id, 'name' => 'Módulo ' . $m . ': Tema ' . rand(100, 999)],
                    ['credits' => rand(2, 4), 'period_number' => $isDegree ? rand(1, 10) : null]
                );

                for ($s = 0; $s < rand(1, 2); $s++) {
                    $randomStart = $horasInicio[array_rand($horasInicio)];
                    // Fechas dinámicas base a HOY para que el calendario las pinte correctamente!!
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->addMonths(4)->endOfMonth();

                    CourseSchedule::updateOrCreate(
                        [
                            'module_id' => $module->id,
                            'section_name' => 'Sección ' . ($s + 1),
                        ],
                        [
                            'teacher_id' => $teachers->random()->id,
                            'classroom_id' => $classrooms->random()->id,
                            'days_of_week' => [$diasSemana[array_rand($diasSemana)], $diasSemana[array_rand($diasSemana)]],
                            'start_time' => $randomStart,
                            'end_time' => Carbon::parse($randomStart)->addHours(2)->format('H:i'),
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'status' => 'Programada',
                            'capacity' => rand(20, 40),
                        ]
                    );
                }
            }
            $this->command->getOutput()->progressAdvance();
        }
        $this->command->getOutput()->progressFinish();

        // --- 4. Crear Estudiantes ---
        $totalStudents = 100;
        $this->command->getOutput()->progressStart($totalStudents);

        for ($i = 1; $i <= $totalStudents; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $user = User::firstOrCreate(
                ['email' => 'estudiante' . $i . '@sga.com'],
                ['name' => $firstName . ' ' . $lastName, 'password' => Hash::make('password')]
            )->assignRole($studentRole);

            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'course_id' => $courses->random()->id, // Asignar a Carrera
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $user->email,
                    'cedula' => '001-' . str_pad($i, 7, '0', STR_PAD_LEFT) . '-0', 
                    'birth_date' => Carbon::now()->subYears(rand(18, 25))->subDays(rand(1, 365)),
                    'mobile_phone' => '809-555-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    'address' => 'Calle ' . rand(1, 100) . ', SDO',
                    'status' => 'Activa',
                    'is_minor' => false,
                ]
            );
            $this->command->getOutput()->progressAdvance();
        }
        $this->command->getOutput()->progressFinish();

        $this->command->info('Mega Seeder Ejecutado: Edificios, Aulas, Carreras y Calendarios creados exitosamente.');
    }
}