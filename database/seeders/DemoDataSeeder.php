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
        // --- 1. Obtener Roles ---
        $teacherRole = Role::where('name', 'Profesor')->first();
        $studentRole = Role::where('name', 'Estudiante')->first();

        if (!$teacherRole || !$studentRole) {
            $this->command->error('Los roles "Profesor" o "Estudiante" no se encontraron.');
            $this->command->error('Asegúrate de ejecutar RolesAndPermissionsSeeder primero.');
            return;
        }

        // --- 2. Crear Profesores ---
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
        $studentNames = [
            'Roberto Pérez', 'Carmen Morales', 'Jorge Castillo', 'Teresa Jiménez', 'Ricardo Navarro', 'Mónica Herrera', 'Fernando Díaz', 'Gabriela Ponce', 'Arturo Reyes', 'Verónica Salazar',
            'Adrián Mendoza', 'Natalia Cordero', 'Héctor Guzmán', 'Daniela Vargas', 'Raúl Domínguez', 'Paula Soto', 'Sergio Aguilera', 'Isabel Guerrero', 'Óscar Ríos', 'Lorena Paredes',
            'Javier Solís', 'Beatriz Ortega', 'Emilio Flores', 'Alejandra Medina', 'Manuel Bravo', 'Clara Núñez', 'Benjamín Vega', 'Rosa Campos', 'Andrés Chávez', 'Fátima Rojas',
            'César Lara', 'Diana Aguilar', 'Iván Osorio', 'Jimena Cruz', 'Samuel Peña', 'Marina Beltrán', 'Mateo Ibáñez', 'Laura Montes', 'Diego Figueroa', 'Valentina Alonso',
            'Gustavo Márquez', 'Valeria Ramos', 'Ignacio Delgado', 'Julieta Gil', 'Simón Acosta', 'Renata Salas', 'Bruno Romero', 'Carolina Mora', 'Leonardo Pineda', 'Elisa Cervantes'
        ];

        foreach ($studentNames as $index => $name) {
            $email = 'estudiante' . ($index + 1) . '@sga.com';
            
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
            $testCedula = '001-' . str_pad($index + 1, 7, '0', STR_PAD_LEFT) . '-0';

            // Crear el perfil de estudiante asociado
            // Usamos firstOrCreate basado en el user_id o la cédula para evitar duplicados
            Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => explode(' ', $name)[0],
                    'last_name' => explode(' ', $name)[1] ?? 'Apellido',
                    'email' => $email,
                    
                    // --- ¡¡¡CORRECCIONES!!! ---
                    // 1. La columna es 'cedula', no 'student_id_number'.
                    'cedula' => $testCedula, 
                    
                    // 2. La columna es 'birth_date', no 'date_of_birth'.
                    'birth_date' => Carbon::createFromDate(2000, 1, 1)->subYears(rand(0, 5)),
                    
                    // 3. La columna es 'mobile_phone' (de la migración ...001) o 'phone' (de la ...013)
                    // Usaremos 'mobile_phone' que es más común.
                    'mobile_phone' => '809-555-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'address' => 'Calle Ficticia 123, Sector ' . ($index + 1),
                    'status' => 'Activa',
                    'is_minor' => false,
                ]
            );
        }
        $this->command->info(count($studentNames) . ' estudiantes creados.');

        // --- 4. Crear Cursos, Módulos y Secciones ---
        $courseData = [
            'Matemáticas Avanzadas' => ['Álgebra Lineal', 'Cálculo Vectorial', 'Ecuaciones Diferenciales', 'Geometría Analítica'],
            'Programación Web' => ['HTML y CSS', 'JavaScript Moderno', 'Backend con PHP', 'Bases de Datos SQL'],
            'Historia del Arte' => ['Renacimiento', 'Barroco', 'Impresionismo', 'Arte Moderno'],
            'Finanzas Corporativas' => ['Análisis Financiero', 'Mercados de Capital', 'Gestión de Riesgos', 'Valoración de Empresas'],
            'Biología Molecular' => ['Genética', 'Microbiología', 'Bioquímica', 'Biología Celular'],
            'Marketing Digital' => ['SEO y SEM', 'Redes Sociales', 'Email Marketing', 'Análisis de Datos'],
            'Diseño Gráfico' => ['Teoría del Color', 'Tipografía', 'Adobe Illustrator', 'Diseño UX/UI'],
            'Literatura Comparada' => ['Novela Moderna', 'Poesía del Siglo XX', 'Teatro Clásico', 'Ensayo Literario'],
            'Inteligencia Artificial' => ['Machine Learning', 'Deep Learning', 'Procesamiento de Lenguaje Natural', 'Visión por Computadora'],
            'Gestión de Proyectos' => ['Metodologías Ágiles (Scrum)', 'Planificación y Costos', 'Gestión de Equipos', 'Calidad y Riesgos'],
            'Física Cuántica' => ['Mecánica Cuántica', 'Relatividad', 'Física de Partículas'],
            'Química Orgánica' => ['Nomenclatura', 'Reacciones Químicas', 'Espectroscopía'],
            'Derecho Romano' => ['Instituciones', 'Obligaciones y Contratos', 'Derecho de Sucesiones'],
            'Arquitectura de Software' => ['Patrones de Diseño', 'Microservicios', 'Arquitecturas Cloud', 'DevOps'],
            'Gastronomía Molecular' => ['Técnicas Básicas', 'Química de Alimentos', 'Maridaje', 'Cocina al Vacío'],
        ];

        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $horasInicio = ['08:00', '10:00', '12:00', '14:00', '16:00'];
        
        $courseCount = 0;
        $moduleCount = 0;
        $scheduleCount = 0;

        foreach ($courseData as $courseName => $modulesList) {
            $course = Course::firstOrCreate(
                ['name' => $courseName],
                [
                    'code' => 'C-' . strtoupper(Str::slug(substr($courseName, 0, 10))),
                    'credits' => rand(3, 5),
                ]
            );
            $courseCount++;

            foreach ($modulesList as $moduleName) {
                $module = Module::firstOrCreate(
                    ['course_id' => $course->id, 'name' => $moduleName],
                    [] // No se necesitan más campos para el firstOrCreate
                );
                $moduleCount++;

                // Crear 1 o 2 secciones (horarios) por módulo
                for ($i = 0; $i < rand(1, 2); $i++) {
                    $randomDay1 = $diasSemana[array_rand($diasSemana)];
                    $randomDay2 = $diasSemana[array_rand($diasSemana)];
                    $randomStart = $horasInicio[array_rand($horasInicio)];

                    CourseSchedule::firstOrCreate(
                        [
                            'module_id' => $module->id,
                            'section_name' => 'Sección ' . ($i + 1),
                        ],
                        [
                            'teacher_id' => $teachers->random()->id,
                            'days_of_week' => [$randomDay1, $randomDay2], // Usa la columna JSON
                            'start_time' => $randomStart,
                            'end_time' => Carbon::parse($randomStart)->addHours(2)->format('H:i'),
                            'start_date' => '2025-01-15',
                            'end_date' => '2025-05-30',
                            'status' => 'Programada',
                            'capacity' => rand(20, 30),
                        ]
                    );
                    $scheduleCount++;
                }
            }
        }
        $this->command->info("$courseCount cursos, $moduleCount módulos y $scheduleCount secciones creadas.");
    }
}