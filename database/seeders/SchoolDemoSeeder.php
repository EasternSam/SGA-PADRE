<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\TeacherAssignment;
use App\Models\Guardian;
use App\Models\SchoolEnrollment;
use App\Models\StudentGrade;
use App\Models\StudentAttendance;
use App\Models\DisciplineRecord;
use App\Models\StudentPayment;
use App\Models\OrientationRecord;
use App\Models\CommunicationLog;
use App\Models\SchoolAnnouncement;
use App\Models\SchoolCalendar;
use App\Models\AuditLog;
use App\Models\SchoolAlert;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Carbon\Carbon;

class SchoolDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando datos de demostración escolar...');

        $admin = User::where('email', 'admin@admin.com')->first();

        // ─── AÑO ACADÉMICO ───────────────────────────────
        $year = AcademicYear::firstOrCreate([
            'name'       => '2025-2026',
        ], [
            'start_date' => '2025-09-01',
            'end_date'   => '2026-06-30',
            'status'     => 'active', // enum: planning, active, closed
        ]);
        $this->command->info('  Año académico 2025-2026');

        // ─── PERÍODOS DE EVALUACIÓN ──────────────────────
        // enum status: upcoming, active, grading, closed
        $periods = [];
        $periodData = [
            ['1er Período',  1, '2025-09-01', '2025-11-30', 'closed'],
            ['2do Período',  2, '2025-12-01', '2026-02-28', 'closed'],
            ['3er Período',  3, '2026-03-01', '2026-04-30', 'active'],
            ['4to Período',  4, '2026-05-01', '2026-06-30', 'upcoming'],
        ];
        foreach ($periodData as [$name, $num, $start, $end, $status]) {
            $periods[] = EvaluationPeriod::firstOrCreate([
                'academic_year_id' => $year->id,
                'number'           => $num,
            ], [
                'name'             => $name,
                'start_date'       => $start,
                'end_date'         => $end,
                'status'           => $status,
            ]);
        }
        $this->command->info('  4 Períodos de evaluación');

        // ─── GRADOS (MINERD) ─────────────────────────────
        // enum level: inicial, primario, secundario
        $gradeLevels = [];
        $gradeData = [
            ['1ro Primaria',  '1ro', 'primario',   1, 1, 65],
            ['2do Primaria',  '2do', 'primario',   1, 2, 65],
            ['3ro Primaria',  '3ro', 'primario',   1, 3, 65],
            ['4to Primaria',  '4to', 'primario',   2, 4, 65],
            ['5to Primaria',  '5to', 'primario',   2, 5, 65],
            ['6to Primaria',  '6to', 'primario',   2, 6, 65],
            ['1ro Secundaria','1ro Sec', 'secundario', 1, 7, 70],
            ['2do Secundaria','2do Sec', 'secundario', 1, 8, 70],
            ['3ro Secundaria','3ro Sec', 'secundario', 2, 9, 70],
            ['4to Secundaria','4to Sec', 'secundario', 2, 10, 70],
        ];
        foreach ($gradeData as $i => [$name, $short, $level, $cycle, $gradeNum, $minScore]) {
            $gradeLevels[] = GradeLevel::firstOrCreate([
                'level' => $level,
                'cycle' => $cycle,
                'grade_number' => $gradeNum,
                'modality' => null,
            ], [
                'name' => $name,
                'short_name' => $short,
                'min_passing_score' => $minScore,
                'order' => $i + 1,
                'is_active' => true,
            ]);
        }
        $this->command->info('  10 Grados escolares');

        // ─── ASIGNATURAS ─────────────────────────────────
        $subjectData = [
            ['Lengua Española',       'LE',   'Lengua',       true,  5],
            ['Matemáticas',           'MAT',  'Matemática',   true,  5],
            ['Ciencias Naturales',    'CN',   'Ciencias',     true,  4],
            ['Ciencias Sociales',     'CS',   'Ciencias',     true,  4],
            ['Inglés',                'ING',  'Lenguas Extr.',true,  3],
            ['Educación Física',      'EF',   'Ed. Física',   false, 2],
            ['Educación Artística',   'EA',   'Artística',    false, 2],
            ['Formación Humana',      'FH',   'Valores',      false, 2],
            ['Informática',           'INF',  'Tecnología',   false, 2],
            ['Francés',               'FRA',  'Lenguas Extr.',false, 2],
        ];
        $subjects = [];
        foreach ($subjectData as [$name, $code, $area, $core, $hours]) {
            $subjects[] = Subject::firstOrCreate([
                'code' => $code,
            ], [
                'name' => $name,
                'area' => $area,
                'is_core' => $core,
                'weekly_hours' => $hours,
                'is_active' => true,
            ]);
        }
        $this->command->info('  10 Asignaturas');

        // ─── DOCENTES ────────────────────────────────────
        $teacherNames = [
            'María Rodríguez', 'Juan Pérez', 'Ana Martínez', 'Carlos López',
            'Rosa Sánchez', 'Pedro Hernández', 'Luisa García', 'Miguel Díaz',
        ];
        $teachers = [];
        foreach ($teacherNames as $i => $name) {
            $email = ($i === 0) ? 'profesor@colegio.edu.do' : 'profesor' . $i . '@colegio.edu.do';
            $u = User::firstOrCreate([
                'email' => $email,
            ], [
                'name' => $name,
                'password' => bcrypt('Password'),
            ]);
            if (!$u->hasRole('Profesor')) {
                $u->assignRole('Profesor');
            }
            $teachers[] = $u;
        }
        $this->command->info('  8 Docentes');

        // ─── SECCIONES ───────────────────────────────────
        $sections = [];
        $sectionNames = ['A', 'B'];
        foreach ($gradeLevels as $gi => $grade) {
            $numSections = $gi < 6 ? 2 : 1; // primaria: 2, secundaria: 1
            for ($s = 0; $s < $numSections; $s++) {
                $teacherIdx = ($gi + $s) % count($teachers);
                $sections[] = Section::firstOrCreate([
                    'academic_year_id' => $year->id,
                    'grade_level_id'   => $grade->id,
                    'name'             => $sectionNames[$s],
                ], [
                    'full_name'           => $grade->short_name . ' ' . $sectionNames[$s],
                    'homeroom_teacher_id' => $teachers[$teacherIdx]->id,
                    'capacity'            => 35,
                    'is_active'           => true,
                ]);
            }
        }
        $this->command->info('  ' . count($sections) . ' Secciones');

        // ─── ASIGNAR ASIGNATURAS A SECCIONES ─────────────
        foreach ($sections as $section) {
            $numSubjects = rand(7, 10);
            for ($si = 0; $si < $numSubjects && $si < count($subjects); $si++) {
                $teacherIdx = $si % count($teachers);
                SectionSubject::firstOrCreate([
                    'section_id' => $section->id,
                    'subject_id' => $subjects[$si]->id,
                ], [
                    'teacher_id' => $teachers[$teacherIdx]->id,
                ]);
            }
        }
        $this->command->info('  Asignaturas asignadas a secciones');

        // ─── ASIGNACIONES DOCENTES ───────────────────────
        foreach ($sections as $section) {
            $subs = SectionSubject::where('section_id', $section->id)->get();
            foreach ($subs as $ss) {
                TeacherAssignment::firstOrCreate([
                    'section_id'       => $section->id,
                    'subject_id'       => $ss->subject_id,
                ], [
                    'academic_year_id' => $year->id,
                    'teacher_id'       => $ss->teacher_id,
                    'is_homeroom'      => $ss->teacher_id === $section->homeroom_teacher_id
                ]);
            }
        }
        $this->command->info('  Asignaciones docentes');

        // ─── ESTUDIANTES ─────────────────────────────────
        $firstNames = [
            'Sofía', 'Mateo', 'Valentina', 'Santiago', 'Isabella', 'Sebastián',
            'Camila', 'Nicolás', 'Luciana', 'Daniel', 'Gabriela', 'Alejandro',
            'Mariana', 'Diego', 'Valeria', 'Andrés', 'Fernanda', 'Samuel',
            'Daniela', 'Emilio', 'Carolina', 'Tomás', 'Andrea', 'Rafael',
            'Paula', 'Adrián', 'Natalia', 'Felipe', 'Laura', 'Julián',
            'Ana', 'Cristian', 'Elena', 'David', 'María', 'Luis',
            'Paola', 'José', 'Claudia', 'Manuel', 'Victoria', 'Roberto',
            'Sara', 'Héctor', 'Diana', 'Fernando', 'Angélica', 'Miguel',
            'Lorena', 'Ricardo', 'Karla', 'Marcos', 'Esther', 'Eduardo',
            'Bianca', 'Pedro', 'Ivanna', 'Pablo', 'Yamilet', 'Bruno',
        ];
        $lastNames = [
            'Pérez García', 'Rodríguez López', 'Martínez Hernández', 'González Díaz',
            'López Sánchez', 'Hernández Cruz', 'Ramírez Torres', 'Flores Rivera',
            'Gómez Ramos', 'Morales Ortiz', 'Jiménez Castillo', 'Ruiz Romero',
            'Vargas Reyes', 'Medina Peña', 'Castro Herrera', 'Guzmán Mendoza',
            'Núñez Acosta', 'Santos Figueroa', 'De la Rosa Matos', 'Almánzar Polanco',
        ];
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $sectors = ['Los Jardines', 'Villa Mella', 'Alma Rosa', 'La Esperilla', 'Arroyo Hondo', 'Naco', 'Gazcue', 'Piantini'];
        $cities = ['Santo Domingo', 'Santiago', 'La Vega', 'San Cristóbal', 'Puerto Plata'];

        $students = [];
        $studentIdx = 0;
        foreach ($sections as $section) {
            $numStudents = rand(12, 20);
            for ($si = 0; $si < $numStudents; $si++) {
                $firstName = $firstNames[$studentIdx % count($firstNames)];
                $lastName  = $lastNames[$studentIdx % count($lastNames)];
                $code      = 'EST-' . str_pad($studentIdx + 1, 4, '0', STR_PAD_LEFT);
                $ced       = sprintf('%03d-%07d-%d', rand(1, 402), rand(1, 9999999), rand(0, 9));
                $age       = rand(6, 17);
                $bdate     = now()->subYears($age)->subDays(rand(0, 365));
                $gender    = $si % 2 === 0 ? 'Femenino' : 'Masculino';

                $student = Student::firstOrCreate([
                    'student_code'           => $code,
                ], [
                    'first_name'             => $firstName,
                    'last_name'              => $lastName,
                    'cedula'                 => $ced,
                    'email'                  => strtolower(str_replace(' ', '', $firstName)) . ($studentIdx + 1) . '@mail.com',
                    'gender'                 => $gender,
                    'birth_date'             => $bdate,
                    'nationality'            => 'Dominicana',
                    'address'                => 'Calle ' . rand(1, 50) . ' #' . rand(1, 200) . ', ' . $sectors[array_rand($sectors)],
                    'city'                   => $cities[array_rand($cities)],
                    'mobile_phone'           => '809-' . rand(200, 999) . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    'status'                 => 'Activo',
                    'is_minor'               => true,
                    'grade_level_id'         => $section->grade_level_id,
                    'section_id'             => $section->id,
                    'academic_year_id'       => $year->id,
                    'enrollment_date'        => '2025-08-15',
                    'blood_type'             => $bloodTypes[array_rand($bloodTypes)],
                    'emergency_contact_name' => $firstNames[rand(0, count($firstNames) - 1)] . ' ' . $lastNames[rand(0, count($lastNames) - 1)],
                    'emergency_contact_phone'=> '809-' . rand(200, 999) . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                ]);
                $students[] = $student;
                $studentIdx++;
            }
        }
        $this->command->info('  ' . count($students) . ' Estudiantes');

        // ─── TUTORES / PADRES ────────────────────────────
        // enum relationship: padre, madre, tutor, abuelo, abuela, tio, tia, otro
        $guardianCount = 0;
        $relationships = ['padre', 'madre', 'tutor', 'abuelo', 'abuela'];
        $occupations = ['Abogado', 'Médico', 'Ingeniero', 'Profesor', 'Comerciante', 'Contador', 'Ama de casa', 'Enfermera'];
        foreach ($students as $student) {
            if (rand(1, 100) <= 75) { // 75% tienen tutor registrado
                Guardian::firstOrCreate([
                    'email'                => 'tutor' . $student->id . '@mail.com',
                ], [
                    'first_name'           => $firstNames[rand(0, count($firstNames) - 1)],
                    'last_name'            => $lastNames[rand(0, count($lastNames) - 1)],
                    'cedula'               => sprintf('%03d-%07d-%d', rand(1, 402), rand(1, 9999999), rand(0, 9)),
                    'phone'                => '809-' . rand(200, 999) . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    'relationship'         => $relationships[array_rand($relationships)],
                    'occupation'           => $occupations[array_rand($occupations)],
                    'is_emergency_contact' => true,
                ]);
                $guardianCount++;
            }
        }
        $this->command->info("  $guardianCount Tutores/Padres");

        // ─── INSCRIPCIONES ───────────────────────────────
        // enum status: pending, approved, enrolled, transferred_out, withdrawn, graduated
        // enum enrollment_type: new, renewal, transfer
        foreach ($students as $student) {
            SchoolEnrollment::firstOrCreate([
                'student_id'             => $student->id,
                'academic_year_id'       => $year->id,
            ], [
                'grade_level_id'         => $student->grade_level_id,
                'section_id'             => $student->section_id,
                'enrollment_code'        => 'INS-' . $year->id . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT),
                'status'                 => 'enrolled',
                'enrollment_type'        => rand(0, 1) ? 'new' : 'renewal',
                'enrollment_date'        => '2025-08-15',
                'doc_birth_certificate'  => true,
                'doc_photos'             => (bool) rand(0, 1),
                'doc_grades_record'      => (bool) rand(0, 1),
                'doc_medical_certificate'=> (bool) rand(0, 1),
                'doc_vaccination_card'   => (bool) rand(0, 1),
                'doc_parent_id'          => (bool) rand(0, 1),
                'doc_report_card'        => (bool) rand(0, 1),
                'doc_good_conduct'       => (bool) rand(0, 1),
                'processed_by'           => $admin?->id,
            ]);
        }
        $this->command->info('  Inscripciones');

        // ─── CALIFICACIONES ──────────────────────────────
        // enum performance_level: destacado, logro_evidenciado, en_proceso, insuficiente
        $gradeCount = 0;
        foreach ($students as $student) {
            $sectionSubs = SectionSubject::where('section_id', $student->section_id)->get();
            foreach ($sectionSubs as $ss) {
                foreach ($periods as $pi => $period) {
                    if ($pi > 2) continue; // P4 sin notas
                    if ($pi === 2 && rand(0, 1) === 0) continue; // P3 parcial

                    $score = rand(55, 100);
                    $level = match(true) {
                        $score >= 89 => 'destacado',
                        $score >= 77 => 'logro_evidenciado',
                        $score >= 65 => 'en_proceso',
                        default      => 'insuficiente',
                    };
                    StudentGrade::firstOrCreate([
                        'student_id'          => $student->id,
                        'section_subject_id'  => $ss->id,
                        'evaluation_period_id'=> $period->id,
                    ], [
                        'score'               => $score,
                        'performance_level'   => $level,
                        'is_recovery'         => $score < 65 && (bool) rand(0, 1),
                        'is_extraordinary'    => false,
                        'recorded_by'         => $ss->teacher_id,
                        'recorded_at'         => $period->end_date,
                    ]);
                    $gradeCount++;
                }
            }
        }
        $this->command->info("  $gradeCount Calificaciones");

        // ─── ASISTENCIA ──────────────────────────────────
        // enum status: present, absent, late, excused, permission
        $attendanceCount = 0;
        $schoolDays = collect();
        $date = Carbon::parse('2025-09-01');
        while ($date->lte(now()) && $schoolDays->count() < 60) {
            if ($date->isWeekday()) $schoolDays->push($date->copy());
            $date->addDay();
        }
        $recentDays = $schoolDays->slice(-15); // últimos 15 días lectivos

        foreach ($students as $student) {
            foreach ($recentDays as $day) {
                $roll = rand(1, 100);
                $status = match(true) {
                    $roll <= 85 => 'present',
                    $roll <= 92 => 'absent',
                    $roll <= 96 => 'late',
                    default     => 'excused',
                };
                StudentAttendance::firstOrCreate([
                    'student_id'      => $student->id,
                    'section_id'      => $student->section_id,
                    'academic_year_id'=> $year->id,
                    'date'            => $day->toDateString(),
                ], [
                    'status'          => $status,
                    'recorded_by'     => $teachers[0]->id,
                ]);
                $attendanceCount++;
            }
        }
        $this->command->info("  $attendanceCount Asistencias");

        // ─── DISCIPLINA ──────────────────────────────────
        // enum severity: leve, grave, muy_grave
        $categories = ['puntualidad', 'uniforme', 'conducta', 'agresion_verbal', 'uso_celular'];
        $severities = ['leve', 'grave', 'muy_grave'];
        $discCount  = 0;
        foreach ($students as $student) {
            if (rand(1, 100) <= 12) { // 12% tienen registro
                DisciplineRecord::firstOrCreate([
                    'student_id'      => $student->id,
                    'academic_year_id'=> $year->id,
                    'date'            => now()->subDays(rand(1, 90))->toDateString(),
                ], [
                    'severity'        => $severities[array_rand($severities)],
                    'category'        => $categories[array_rand($categories)],
                    'description'     => 'Incidente registrado durante la jornada escolar.',
                    'action_taken'    => ['Llamada de atención', 'Nota a padres', 'Suspensión 1 día', 'Reporte a dirección'][rand(0, 3)],
                    'reported_by'     => $teachers[array_rand($teachers)]->id,
                    'parent_notified' => (bool) rand(0, 1),
                ]);
                $discCount++;
            }
        }
        $this->command->info("  $discCount Disciplina");

        // ─── PAGOS ───────────────────────────────────────
        // enum type: inscription, monthly, uniform, material, event, other
        // enum status: pending, partial, paid, waived
        // enum method: cash, transfer, card, check, other
        $payCount = 0;
        $months = ['Septiembre','Octubre','Noviembre','Diciembre','Enero','Febrero','Marzo','Abril','Mayo'];
        $methods = ['cash', 'transfer', 'card'];

        foreach ($students as $student) {
            // Inscripción
            $inscAmount = [3500, 4000, 4500, 5000][rand(0, 3)];
            StudentPayment::firstOrCreate([
                'student_id'       => $student->id,
                'academic_year_id' => $year->id,
                'type'             => 'inscription',
                'concept'          => 'Inscripción 2025-2026',
            ], [
                'amount' => $inscAmount, 'paid' => $inscAmount,
                'status' => 'paid', 'due_date' => '2025-08-15',
                'paid_date' => '2025-08-' . rand(10, 28),
                'method' => $methods[array_rand($methods)],
                'recorded_by' => $admin?->id,
            ]);
            $payCount++;

            // Mensualidades
            $monthly = [2500, 3000, 3500][rand(0, 2)];
            foreach ($months as $mi => $month) {
                $dueDate = Carbon::parse('2025-09-05')->addMonths($mi);
                $paid = rand(1, 100) > 10; // 90% pagaron
                if ($dueDate->gt(now())) {
                    StudentPayment::firstOrCreate([
                        'student_id'       => $student->id,
                        'academic_year_id' => $year->id,
                        'type'             => 'monthly',
                        'concept'          => "Mensualidad $month",
                    ], [
                        'amount' => $monthly, 'paid' => 0,
                        'status' => 'pending', 'due_date' => $dueDate,
                        'recorded_by' => $admin?->id,
                    ]);
                } else {
                    StudentPayment::firstOrCreate([
                        'student_id'       => $student->id,
                        'academic_year_id' => $year->id,
                        'type'             => 'monthly',
                        'concept'          => "Mensualidad $month",
                    ], [
                        'amount' => $monthly,
                        'paid' => $paid ? $monthly : 0,
                        'status' => $paid ? 'paid' : 'pending',
                        'due_date' => $dueDate,
                        'paid_date' => $paid ? $dueDate->copy()->addDays(rand(0, 5))->format('Y-m-d') : null,
                        'method' => $paid ? $methods[array_rand($methods)] : null,
                        'recorded_by' => $admin?->id,
                    ]);
                }
                $payCount++;
            }
        }
        $this->command->info("  $payCount Pagos");

        // ─── ORIENTACIÓN ─────────────────────────────────
        // enum type: interview, observation, referral, followup, psychological, family, academic
        // enum priority: low, medium, high, urgent
        // enum status: open, in_progress, resolved, referred
        $orientTypes = ['interview', 'observation', 'referral', 'followup', 'psychological', 'family', 'academic'];
        $orientPriorities = ['low', 'medium', 'high', 'urgent'];
        $orientStatuses = ['open', 'in_progress', 'resolved', 'referred'];
        $orientTitles = ['Dificultad académica', 'Problema conductual', 'Situación familiar', 'Bullying reportado', 'Ansiedad', 'Bajo rendimiento'];
        $orientCount = 0;

        foreach ($students as $student) {
            if (rand(1, 100) <= 8) { // 8%
                $title = $orientTitles[array_rand($orientTitles)];
                OrientationRecord::firstOrCreate([
                    'student_id'      => $student->id,
                    'academic_year_id'=> $year->id,
                    'title'           => $title,
                ], [
                    'type'            => $orientTypes[array_rand($orientTypes)],
                    'description'     => 'Caso registrado para seguimiento por orientación.',
                    'findings'        => rand(0, 1) ? 'Se identificaron factores que requieren atención.' : null,
                    'recommendations' => rand(0, 1) ? 'Seguimiento semanal con consejero.' : null,
                    'priority'        => $orientPriorities[array_rand($orientPriorities)],
                    'status'          => $orientStatuses[array_rand($orientStatuses)],
                    'next_followup'   => rand(0, 1) ? now()->addDays(rand(3, 30)) : null,
                    'counselor_id'    => $admin?->id,
                    'is_confidential' => (bool) rand(0, 1),
                ]);
                $orientCount++;
            }
        }
        $this->command->info("  $orientCount Orientación");

        // ─── COMUNICACIONES ──────────────────────────────
        // enum channel: whatsapp, email, sms, push, internal
        // enum type: individual, section, grade, all
        // enum status: draft, sent, failed
        $commData = [
            ['Reunión de Padres',      'Se convoca a reunión de padres para el próximo viernes a las 5pm.',    'whatsapp', 'all'],
            ['Cambio de Horario',      'Se modifica el horario de clases a partir del lunes.',                 'email',    'all'],
            ['Pago Pendiente',         'Le recordamos que tiene pagos pendientes de mensualidad.',             'sms',      'individual'],
            ['Acto de Navidad',        'Les invitamos al acto navideño el 20 de diciembre a las 6pm.',        'whatsapp', 'all'],
            ['Suspensión por Lluvia',  'Se suspenden las clases mañana por pronóstico de lluvias fuertes.',   'internal', 'all'],
            ['Entrega de Boletines',   'La entrega de boletines del 2do período será el viernes.',            'email',    'section'],
            ['Día del Estudiante',     'Celebraremos el Día del Estudiante con actividades especiales.',      'push',     'all'],
        ];
        foreach ($commData as [$subject, $body, $channel, $type]) {
            $sentAt = now()->subDays(rand(1, 90));
            CommunicationLog::firstOrCreate([
                'subject' => $subject,
                'sent_at' => $sentAt->toDateString(),
            ], [
                'channel' => $channel, 'type' => $type,
                'body' => $body,
                'sent_by' => $admin?->id,
                'recipients_count' => rand(20, 350),
                'status' => 'sent',
            ]);
        }
        $this->command->info('  7 Comunicaciones');

        // ─── CIRCULARES ──────────────────────────────────
        // enum type: circular, announcement, alert, event, memo
        // enum priority: normal, important, urgent
        // enum audience: all, teachers, parents, students, staff
        $annData = [
            ['Inicio de Año Escolar',       'Informamos que las clases inician el 1ro de septiembre. Los esperamos puntuales.', 'circular',     'important',  'all'],
            ['Día de la Bandera',           'Celebraremos el Día de la Bandera con actos cívicos y cultural.',                 'event',        'normal',     'all'],
            ['Exámenes 2do Período',        'Los exámenes del 2do período inician el 25 de noviembre.',                        'announcement', 'important',  'students'],
            ['Jornada de Vacunación',       'Jornada de vacunación en coordinación con Salud Pública.',                        'alert',        'urgent',     'parents'],
            ['Reunión de Docentes',         'Se convoca reunión de planificación para el próximo lunes.',                      'memo',         'normal',     'teachers'],
            ['Festival de la Lectura',      'Gran festival de lectura. Participación abierta a todos los grados.',             'event',        'normal',     'all'],
        ];
        foreach ($annData as [$title, $body, $type, $priority, $audience]) {
            SchoolAnnouncement::firstOrCreate([
                'academic_year_id' => $year->id,
                'title' => $title,
            ], [
                'author_id' => $admin?->id,
                'body' => $body,
                'type' => $type, 'priority' => $priority, 'audience' => $audience,
                'publish_date' => now()->subDays(rand(1, 120)),
                'is_published' => true,
                'requires_acknowledgment' => (bool) rand(0, 1),
            ]);
        }
        $this->command->info('  6 Circulares');

        // ─── CALENDARIO ESCOLAR ──────────────────────────
        // enum type: school_day, holiday, teacher_day, exam_day, event, vacation, makeup_day
        $calData = [
            ['2025-09-01', 'school_day',  'Inicio de Clases'],
            ['2025-09-24', 'holiday',     'Día de las Mercedes'],
            ['2025-11-06', 'holiday',     'Día de la Constitución'],
            ['2025-11-25', 'exam_day',    'Exámenes 1er Período (inicio)'],
            ['2025-12-20', 'event',       'Acto de Navidad'],
            ['2025-12-23', 'vacation',    'Inicio Vacaciones Navidad'],
            ['2026-01-06', 'school_day',  'Regreso a Clases'],
            ['2026-01-21', 'holiday',     'Día de la Altagracia'],
            ['2026-01-26', 'holiday',     'Día de Duarte'],
            ['2026-02-27', 'holiday',     'Día de la Independencia'],
            ['2026-03-02', 'exam_day',    'Exámenes 2do Período (inicio)'],
            ['2026-03-30', 'vacation',    'Semana Santa (inicio)'],
            ['2026-04-06', 'school_day',  'Regreso Semana Santa'],
            ['2026-04-14', 'teacher_day', 'Día del Maestro'],
            ['2026-05-01', 'holiday',     'Día del Trabajo'],
            ['2026-05-26', 'exam_day',    'Exámenes 3er Período (inicio)'],
            ['2026-06-16', 'event',       'Acto de Graduación'],
            ['2026-06-30', 'school_day',  'Último Día Lectivo'],
        ];
        foreach ($calData as [$date, $type, $name]) {
            SchoolCalendar::firstOrCreate([
                'academic_year_id' => $year->id,
                'date' => $date,
            ], [
                'type' => $type, 'name' => $name,
                'affects_attendance' => in_array($type, ['holiday', 'vacation']),
            ]);
        }
        $this->command->info('  ' . count($calData) . ' Calendario');

        // ─── ALERTAS ─────────────────────────────────────
        // enum type: absence_streak, low_performance, dropout_risk, discipline, custom
        // enum severity: info, warning, critical
        $alertTypes = ['absence_streak', 'low_performance', 'dropout_risk', 'discipline', 'custom'];
        $alertSeverities = ['info', 'warning', 'critical'];
        $alertTitles = [
            'absence_streak'  => '5 ausencias consecutivas',
            'low_performance' => 'Promedio por debajo de 65',
            'dropout_risk'    => 'Riesgo de deserción detectado',
            'discipline'      => 'Múltiples incidentes disciplinarios',
            'custom'          => 'Situación especial reportada',
        ];

        $alertStudents = array_slice($students, 0, min(10, count($students)));
        foreach ($alertStudents as $student) {
            $type = $alertTypes[array_rand($alertTypes)];
            SchoolAlert::firstOrCreate([
                'student_id'      => $student->id,
                'academic_year_id'=> $year->id,
                'type'            => $type,
            ], [
                'severity'        => $alertSeverities[array_rand($alertSeverities)],
                'title'           => $alertTitles[$type],
                'description'     => 'Alerta generada por el sistema de seguimiento.',
                'is_resolved'     => (bool) rand(0, 1),
            ]);
        }
        $this->command->info('  ' . count($alertStudents) . ' Alertas');

        // ─── AUDITORÍA ───────────────────────────────────
        $auditActions = ['created', 'updated', 'login', 'exported', 'approved'];
        $auditModels = ['App\\Models\\Student', 'App\\Models\\StudentGrade', 'App\\Models\\StudentPayment', 'App\\Models\\Section'];
        $auditDescs = ['Estudiante creado', 'Nota registrada', 'Pago procesado', 'Sección actualizada', 'Reporte exportado', 'Sesión iniciada'];
        if (AuditLog::count() < 25) {
            for ($i = 0; $i < 25; $i++) {
                AuditLog::create([
                    'user_id'     => ($i % 3 === 0) ? ($admin?->id) : $teachers[array_rand($teachers)]->id,
                    'action'      => $auditActions[array_rand($auditActions)],
                    'model_type'  => $auditModels[array_rand($auditModels)],
                    'model_id'    => rand(1, 50),
                    'description' => $auditDescs[array_rand($auditDescs)],
                    'ip_address'  => '192.168.1.' . rand(1, 254),
                ]);
            }
        }
        $this->command->info('  25 Auditoría');

        // ─── EMPLEADOS Y NÓMINAS (PARA DOCENTES) ──────────
        $this->command->info('Creando expedientes de empleados y nóminas...');
        
        $payroll = Payroll::firstOrCreate([
            'name' => 'Nómina Docente Mayo 2026',
        ], [
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
            'status' => 'Pagado',
            'total_amount' => 0.00,
        ]);
        
        $totalPayrollAmount = 0;

        foreach ($teachers as $i => $teacher) {
            $employee = Employee::firstOrCreate([
                'user_id' => $teacher->id,
            ], [
                'biometric_id' => $teacher->id,
                'position' => 'Docente por Asignación',
                'department' => 'Académico',
                'contract_type' => 'Fijo',
                'base_salary' => 45000.00 + ($i * 1500.00),
                'hourly_rate' => 350.00,
                'hire_date' => '2025-08-15',
                'status' => 'active',
            ]);
            
            $baseAmount = $employee->base_salary;
            $deductions = round($baseAmount * 0.0591, 2);
            $netAmount = $baseAmount - $deductions;
            
            PayrollItem::firstOrCreate([
                'payroll_id' => $payroll->id,
                'employee_id' => $employee->id,
            ], [
                'base_amount' => $baseAmount,
                'deductions' => $deductions,
                'net_amount' => $netAmount,
                'details' => [
                    'formula' => "Sueldo Base RD$ " . number_format($baseAmount, 2) . " - Deducciones de Ley (5.91%)",
                    'notes' => 'Pago mensual ordinario correspondiente al mes de mayo.'
                ]
            ]);

            $totalPayrollAmount += $netAmount;
        }

        $payroll->update(['total_amount' => $totalPayrollAmount]);
        $this->command->info('  Expedientes de empleados y nómina de mayo creados.');

        // ─── USUARIO EXTRA: REGISTRO ─────────────────────
        $regUser = User::firstOrCreate([
            'email' => 'registro@colegio.edu.do',
        ], [
            'name' => 'Encargada de Registro',
            'password' => bcrypt('Password'),
        ]);
        if (!$regUser->hasRole('Registro')) {
            $regUser->assignRole('Registro');
        }
        $this->command->info('  Usuario Registro');

        // ─── RESUMEN FINAL ───────────────────────────────
        $this->command->newLine();
        $this->command->info('¡Datos de demostración creados!');
        $this->command->table(
            ['Entidad', 'Cantidad'],
            [
                ['Estudiantes', count($students)],
                ['Secciones', count($sections)],
                ['Calificaciones', $gradeCount],
                ['Asistencias', $attendanceCount],
                ['Pagos', $payCount],
                ['Tutores', $guardianCount],
                ['Disciplina', $discCount],
                ['Orientación', $orientCount],
                ['Docentes', count($teachers)],
            ]
        );
    }
}
