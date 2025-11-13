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
use Illuminate\Support\Str;
use Carbon\Carbon; // Importar Carbon
use Faker\Factory as Faker; // ¡Importante! Añadir Faker

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Este seeder crea datos de demostración para probar la aplicación.
     * Debe ejecutarse DESPUÉS de RolesAndPermissionsSeeder.
     */
    public function run(): void
    {
        // Inicializar Faker, preferiblemente con localización en español
        $faker = Faker::create('es_ES');

        // --- 1. Obtener Roles ---
        $teacherRole = Role::where('name', 'Profesor')->first();
        $studentRole = Role::where('name', 'Estudiante')->first();

        if (!$teacherRole || !$studentRole) {
            $this->command->error('Los roles "Profesor" o "Estudiante" no se encontraron.');
            $this->command->error('Asegúrate de ejecutar RolesAndPermissionsSeeder primero.');
            return;
        }

        // --- 2. Crear Profesores ---
        // (Mantenemos los 10 profesores originales)
        $teachers = collect();
        $teacherNames = ['Ana Gómez', 'Carlos Ruiz', 'María López', 'Juan Torres', 'Lucía Fernández', 'Pedro Sánchez', 'Elena Vidal', 'Miguel Costa', 'Sofía Romero', 'David Martín'];
        
        foreach ($teacherNames as $index => $name) {
            $teachers->push(
                User::firstOrCreate(
                    ['email' => 'profesor' . ($index + 1) . '@sga.com'],
                    [
                        'name' => $name,
                        'password' => Hash::make('password'),
                    ]
                )->assignRole($teacherRole)
            );
        }
        $this->command->info(count($teacherNames) . ' profesores creados.');

        // --- 3. Crear Estudiantes ---
        // Bucle para crear 1000 estudiantes (AJUSTADO)
        $totalStudents = 1000; // <--- CAMBIO REALIZADO AQUÍ
        $this->command->getOutput()->progressStart($totalStudents); // Barra de progreso

        for ($i = 1; $i <= $totalStudents; $i++) {
            
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $name = $firstName . ' ' . $lastName;
            $email = 'estudiante' . $i . '@sga.com'; // Email único basado en el índice
            
            // Usamos firstOrCreate para evitar duplicados si el seeder se corre varias veces
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                ]
            );
            $user->assignRole($studentRole);

            // Generar una cédula única para la prueba
            $testCedula = '001-' . str_pad($i, 7, '0', STR_PAD_LEFT) . '-0';

            // Crear el perfil de estudiante asociado
            Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'cedula' => $testCedula, 
                    'birth_date' => $faker->dateTimeBetween('-25 years', '-18 years'), // Edades entre 18 y 25
                    'mobile_phone' => $faker->numerify('809-555-####'), // Teléfono ficticio
                    'address' => $faker->address, // Dirección ficticia
                    'status' => 'Activa',
                    'is_minor' => false,
                ]
            );
            
            $this->command->getOutput()->progressAdvance(); // Avanzar barra de progreso
        }
        
        $this->command->getOutput()->progressFinish(); // Terminar barra de progreso
        $this->command->info($totalStudents . ' estudiantes creados.');

        // --- 4. Crear Cursos, Módulos y Secciones ---
        
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $horasInicio = ['08:00', '10:00', '12:00', '14:00', '16:00'];
        
        $courseCount = 0;
        $moduleCount = 0;
        $scheduleCount = 0;
        $totalCourses = 70;

        $this->command->getOutput()->progressStart($totalCourses); // Barra de progreso

        // Bucle para crear 70 cursos
        for ($c = 1; $c <= $totalCourses; $c++) {
            
            // Generar nombre de curso ficticio
            $courseName = $faker->bs; // Nombres como "Plataforma Interactiva" o "Sinergia de Mercado"
            $courseName = 'Curso de ' . $courseName; // "Curso de Plataforma Interactiva"

            $course = Course::firstOrCreate(
                ['code' => 'C-' . str_pad($c, 3, '0', STR_PAD_LEFT)], // Código único C-001, C-002...
                [
                    'name' => $courseName,
                    'credits' => rand(3, 5),
                ]
            );
            $courseCount++;

            // Crear entre 3 y 5 módulos por curso
            $numModules = rand(3, 5);
            for ($m = 1; $m <= $numModules; $m++) {
                
                $moduleName = 'Módulo ' . $m . ': ' . $faker->sentence(4); // Nombre de módulo ficticio

                $module = Module::firstOrCreate(
                    ['course_id' => $course->id, 'name' => $moduleName],
                    [] // No se necesitan más campos para el firstOrCreate
                );
                $moduleCount++;

                // Crear 1 o 2 secciones (horarios) por módulo
                for ($s = 0; $s < rand(1, 2); $s++) {
                    $randomDay1 = $diasSemana[array_rand($diasSemana)];
                    $randomDay2 = $diasSemana[array_rand($diasSemana)];
                    $randomStart = $horasInicio[array_rand($horasInicio)];

                    CourseSchedule::firstOrCreate(
                        [
                            'module_id' => $module->id,
                            'section_name' => 'Sección ' . ($s + 1),
                        ],
                        [
                            'teacher_id' => $teachers->random()->id,
                            'days_of_week' => [$randomDay1, $randomDay2], // Usa la columna JSON
                            'start_time' => $randomStart,
                            'end_time' => Carbon::parse($randomStart)->addHours(2)->format('H:i'),
                            'start_date' => '2025-01-15',
                            'end_date' => '2025-05-30',
                            'status' => 'Programada',
                            'capacity' => rand(20, 40), // Aumenté un poco la capacidad
                        ]
                    );
                    $scheduleCount++;
                }
            }
            $this->command->getOutput()->progressAdvance(); // Avanzar barra de progreso
        }
        
        $this->command->getOutput()->progressFinish(); // Terminar barra de progreso
        $this->command->info("$courseCount cursos, $moduleCount módulos y $scheduleCount secciones creadas.");
    }
}