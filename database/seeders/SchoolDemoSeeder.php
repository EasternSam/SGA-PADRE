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
use Carbon\Carbon;

class SchoolDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏫 Creando datos de demostración escolar...');

        $admin = User::where('email', 'admin@admin.com')->first();

        // ─── AÑO ACADÉMICO ───────────────────────────────
        $year = AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date'   => '2026-06-30',
            'status'     => 'active',
        ]);
        $this->command->info('  ✅ Año académico 2025-2026');

        // ─── PERÍODOS DE EVALUACIÓN ──────────────────────
        $periods = [];
        $periodData = [
            ['1er Período',  1, '2025-09-01', '2025-11-30'],
            ['2do Período',  2, '2025-12-01', '2026-02-28'],
            ['3er Período',  3, '2026-03-01', '2026-04-30'],
            ['4to Período',  4, '2026-05-01', '2026-06-30'],
        ];
        foreach ($periodData as [$name, $num, $start, $end]) {
            $periods[] = EvaluationPeriod::create([
                'academic_year_id' => $year->id,
                'name'             => $name,
                'number'           => $num,
                'start_date'       => $start,
                'end_date'         => $end,
                'status'           => $num <= 2 ? 'closed' : ($num === 3 ? 'active' : 'pending'),
            ]);
        }
        $this->command->info('  ✅ 4 Períodos de evaluación');

        // ─── GRADOS (MINERD) ─────────────────────────────
        $gradeLevels = [];
        $gradeData = [
            ['1ro Primaria',  '1ro', 'primaria',  'primer_ciclo',  1, 65],
            ['2do Primaria',  '2do', 'primaria',  'primer_ciclo',  2, 65],
            ['3ro Primaria',  '3ro', 'primaria',  'primer_ciclo',  3, 65],
            ['4to Primaria',  '4to', 'primaria',  'segundo_ciclo', 4, 65],
            ['5to Primaria',  '5to', 'primaria',  'segundo_ciclo', 5, 65],
            ['6to Primaria',  '6to', 'primaria',  'segundo_ciclo', 6, 65],
            ['1ro Secundaria','1ro Sec', 'secundaria', 'primer_ciclo', 7, 70],
            ['2do Secundaria','2do Sec', 'secundaria', 'primer_ciclo', 8, 70],
            ['3ro Secundaria','3ro Sec', 'secundaria', 'segundo_ciclo', 9, 70],
            ['4to Secundaria','4to Sec', 'secundaria', 'segundo_ciclo', 10, 70],
        ];
        foreach ($gradeData as $i => [$name, $short, $level, $cycle, $gradeNum, $minScore]) {
            $gradeLevels[] = GradeLevel::create([
                'name' => $name, 'short_name' => $short, 'level' => $level,
                'cycle' => $cycle, 'grade_number' => $gradeNum,
                'min_passing_score' => $minScore, 'order' => $i + 1, 'is_active' => true,
            ]);
        }
        $this->command->info('  ✅ 10 Grados escolares (1ro Primaria a 4to Secundaria)');

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
            $subjects[] = Subject::create([
                'name' => $name, 'code' => $code, 'area' => $area,
                'is_core' => $core, 'weekly_hours' => $hours, 'is_active' => true,
            ]);
        }
        $this->command->info('  ✅ 10 Asignaturas');

        // ─── DOCENTES ────────────────────────────────────
        $teacherNames = [
            'María Rodríguez', 'Juan Pérez', 'Ana Martínez', 'Carlos López',
            'Rosa Sánchez', 'Pedro Hernández', 'Luisa García', 'Miguel Díaz',
        ];
        $teachers = [];
        foreach ($teacherNames as $i => $name) {
            $email = 'profesor' . ($i + 1) . '@colegio.edu.do';
            $u = User::create([
                'name' => $name, 'email' => $email,
                'password' => bcrypt('Password'),
            ]);
            $u->assignRole('Profesor');
            $teachers[] = $u;
        }
        $this->command->info('  ✅ 8 Docentes');

        // ─── SECCIONES ───────────────────────────────────
        $sections = [];
        $sectionNames = ['A', 'B'];
        // Crear secciones para grados 1-6 primaria y 1-4 secundaria
        foreach ($gradeLevels as $gi => $grade) {
            $numSections = $gi < 6 ? 2 : 1; // primaria: 2 secciones, secundaria: 1
            for ($s = 0; $s < $numSections; $s++) {
                $teacherIdx = ($gi + $s) % count($teachers);
                $sections[] = Section::create([
                    'academic_year_id'    => $year->id,
                    'grade_level_id'      => $grade->id,
                    'name'                => $sectionNames[$s],
                    'full_name'           => $grade->short_name . ' ' . $sectionNames[$s],
                    'homeroom_teacher_id' => $teachers[$teacherIdx]->id,
                    'capacity'            => 35,
                    'is_active'           => true,
                ]);
            }
        }
        $this->command->info('  ✅ ' . count($sections) . ' Secciones');

        // ─── ASIGNAR ASIGNATURAS A SECCIONES ─────────────
        $sectionSubjects = [];
        foreach ($sections as $section) {
            // Primeras 7 asignaturas para todos, las últimas 3 opcionales
            $numSubjects = rand(7, 10);
            for ($si = 0; $si < $numSubjects && $si < count($subjects); $si++) {
                $teacherIdx = $si % count($teachers);
                $ss = SectionSubject::create([
                    'section_id' => $section->id,
                    'subject_id' => $subjects[$si]->id,
                    'teacher_id' => $teachers[$teacherIdx]->id,
                ]);
                $sectionSubjects[] = $ss;
            }
        }
        $this->command->info('  ✅ Asignaturas asignadas a secciones');

        // ─── ASIGNACIONES DOCENTES ───────────────────────
        foreach ($sections as $section) {
            $subs = SectionSubject::where('section_id', $section->id)->get();
            foreach ($subs as $ss) {
                TeacherAssignment::firstOrCreate([
                    'academic_year_id' => $year->id,
                    'teacher_id'       => $ss->teacher_id,
                    'section_id'       => $section->id,
                    'subject_id'       => $ss->subject_id,
                ], ['is_homeroom' => $ss->teacher_id === $section->homeroom_teacher_id]);
            }
        }
        $this->command->info('  ✅ Asignaciones docentes');

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

        $students = [];
        $studentIdx = 0;
        foreach ($sections as $section) {
            $numStudents = rand(15, 25);
            for ($si = 0; $si < $numStudents; $si++) {
                $firstName = $firstNames[$studentIdx % count($firstNames)];
                $lastName  = $lastNames[$studentIdx % count($lastNames)];
                $code      = 'EST-' . str_pad($studentIdx + 1, 4, '0', STR_PAD_LEFT);
                $ced       = sprintf('%03d-%07d-%d', rand(1, 402), rand(1, 9999999), rand(0, 9));
                $age       = rand(6, 17);
                $bdate     = now()->subYears($age)->subDays(rand(0, 365));
                $gender    = $si % 2 === 0 ? 'Femenino' : 'Masculino';
                $isMinor   = $age < 18;

                $student = Student::create([
                    'student_code'           => $code,
                    'first_name'             => $firstName,
                    'last_name'              => $lastName,
                    'cedula'                 => $ced,
                    'email'                  => strtolower(str_replace(' ', '', $firstName)) . ($studentIdx + 1) . '@mail.com',
                    'gender'                 => $gender,
                    'birth_date'             => $bdate,
                    'nationality'            => 'Dominicana',
                    'address'                => 'Calle ' . rand(1, 50) . ' #' . rand(1, 200) . ', Sector ' . ['Los Jardines', 'Villa Mella', 'Alma Rosa', 'La Esperilla', 'Arroyo Hondo', 'Naco'][rand(0, 5)],
                    'city'                   => ['Santo Domingo', 'Santiago', 'La Vega', 'San Cristóbal', 'Puerto Plata'][rand(0, 4)],
                    'mobile_phone'           => '809-' . rand(200, 999) . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    'status'                 => 'Activo',
                    'is_minor'               => $isMinor,
                    'grade_level_id'         => $section->grade_level_id,
                    'section_id'             => $section->id,
                    'academic_year_id'       => $year->id,
                    'enrollment_date'        => '2025-08-15',
                    'blood_type'             => $bloodTypes[array_rand($bloodTypes)],
                    'emergency_contact_name' => $lastNames[rand(0, count($lastNames) - 1)],
                    'emergency_contact_phone'=> '809-' . rand(200, 999) . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                ]);
                $students[] = $student;
                $studentIdx++;
            }
        }
        $this->command->info('  ✅ ' . count($students) . ' Estudiantes');

        // ─── TUTORES / PADRES ────────────────────────────
        $guardianCount = 0;
        foreach ($students as $student) {
            if (rand(1, 100) > 30) { // 70% tienen tutor registrado
                $rel = ['padre', 'madre', 'tutor', 'abuelo', 'abuela'][rand(0, 4)];
                Guardian::create([
                    'first_name'           => $firstNames[rand(0, count($firstNames) - 1)],
                    'last_name'            => $lastNames[rand(0, count($lastNames) - 1)],
                    'cedula'               => sprintf('%03d-%07d-%d', rand(1, 402), rand(1, 9999999), rand(0, 9)),
                    'phone'                => '809-' . rand(200, 999) . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    'email'                => 'tutor' . $student->id . '@mail.com',
                    'relationship'         => $rel,
                    'occupation'           => ['Abogado', 'Médico', 'Ingeniero', 'Profesor', 'Comerciante', 'Contador', 'Ama de casa'][rand(0, 6)],
                    'is_emergency_contact' => true,
                ]);
                // Vincular via pivot si existe
                if (method_exists(Guardian::class, 'students')) {
                    // skip pivot for now
                }
                $guardianCount++;
            }
        }
        $this->command->info("  ✅ $guardianCount Tutores/Padres");

        // ─── INSCRIPCIONES ───────────────────────────────
        foreach ($students as $student) {
            SchoolEnrollment::create([
                'student_id'             => $student->id,
                'academic_year_id'       => $year->id,
                'grade_level_id'         => $student->grade_level_id,
                'section_id'             => $student->section_id,
                'enrollment_code'        => 'INS-' . $year->id . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT),
                'status'                 => 'active',
                'enrollment_type'        => rand(0, 1) ? 'new' : 'returning',
                'enrollment_date'        => '2025-08-15',
                'doc_birth_certificate'  => true,
                'doc_photos'             => rand(0, 1),
                'doc_grades_record'      => rand(0, 1),
                'doc_medical_certificate'=> rand(0, 1),
                'doc_vaccination_card'   => rand(0, 1),
                'doc_parent_id'          => rand(0, 1),
                'doc_report_card'        => rand(0, 1),
                'doc_good_conduct'       => rand(0, 1),
                'processed_by'           => $admin?->id,
            ]);
        }
        $this->command->info('  ✅ Inscripciones escolares');

        // ─── CALIFICACIONES (P1 y P2 completas, P3 parcial) ─
        $gradeCount = 0;
        foreach ($students as $student) {
            $sectionSubjectsForStudent = SectionSubject::where('section_id', $student->section_id)->get();
            foreach ($sectionSubjectsForStudent as $ss) {
                foreach ($periods as $pi => $period) {
                    if ($pi > 2) continue; // P4 sin notas
                    if ($pi === 2 && rand(0, 1) === 0) continue; // P3 parcial

                    $score = rand(55, 100);
                    $level = match(true) {
                        $score >= 90 => 'A',
                        $score >= 80 => 'B',
                        $score >= 70 => 'C',
                        $score >= 60 => 'D',
                        default      => 'F',
                    };
                    StudentGrade::create([
                        'student_id'          => $student->id,
                        'section_subject_id'  => $ss->id,
                        'evaluation_period_id'=> $period->id,
                        'score'               => $score,
                        'performance_level'   => $level,
                        'is_recovery'         => $score < 65 && rand(0, 1),
                        'is_extraordinary'    => false,
                        'recorded_by'         => $ss->teacher_id,
                        'recorded_at'         => $period->end_date,
                    ]);
                    $gradeCount++;
                }
            }
        }
        $this->command->info("  ✅ $gradeCount Calificaciones");

        // ─── ASISTENCIA (últimos 60 días lectivos) ───────
        $attendanceCount = 0;
        $schoolDays = collect();
        $date = Carbon::parse('2025-09-01');
        while ($date->lte(now()) && $schoolDays->count() < 60) {
            if ($date->isWeekday()) {
                $schoolDays->push($date->copy());
            }
            $date->addDay();
        }

        // Solo últimos 20 días para no saturar la BD
        $recentDays = $schoolDays->slice(-20);
        foreach ($students as $student) {
            foreach ($recentDays as $day) {
                $roll = rand(1, 100);
                $status = match(true) {
                    $roll <= 85 => 'present',
                    $roll <= 92 => 'absent',
                    $roll <= 96 => 'tardy',
                    default     => 'excused',
                };
                StudentAttendance::create([
                    'student_id'      => $student->id,
                    'section_id'      => $student->section_id,
                    'academic_year_id'=> $year->id,
                    'date'            => $day,
                    'status'          => $status,
                    'recorded_by'     => $teachers[0]->id,
                ]);
                $attendanceCount++;
            }
        }
        $this->command->info("  ✅ $attendanceCount Registros de asistencia");

        // ─── DISCIPLINA ──────────────────────────────────
        $categories = ['puntualidad', 'uniforme', 'conducta', 'agresion_verbal', 'uso_celular'];
        $severities = ['leve', 'grave', 'muy_grave'];
        $discCount  = 0;
        foreach ($students as $student) {
            if (rand(1, 100) > 85) { // 15% tienen registro
                $severity = $severities[rand(0, 2)];
                DisciplineRecord::create([
                    'student_id'      => $student->id,
                    'academic_year_id'=> $year->id,
                    'date'            => now()->subDays(rand(1, 90)),
                    'severity'        => $severity,
                    'category'        => $categories[rand(0, count($categories) - 1)],
                    'description'     => 'Incidente registrado durante la jornada escolar.',
                    'action_taken'    => ['Llamada de atención', 'Nota al padre', 'Suspensión 1 día', 'Reporte a dirección'][rand(0, 3)],
                    'reported_by'     => $teachers[rand(0, count($teachers) - 1)]->id,
                    'parent_notified' => rand(0, 1),
                ]);
                $discCount++;
            }
        }
        $this->command->info("  ✅ $discCount Registros de disciplina");

        // ─── PAGOS ───────────────────────────────────────
        $payCount = 0;
        $months = ['Septiembre', 'Octubre', 'Noviembre', 'Diciembre', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'];
        foreach ($students as $student) {
            // Inscripción
            $inscAmount = [3500, 4000, 4500, 5000][rand(0, 3)];
            StudentPayment::create([
                'student_id'      => $student->id,
                'academic_year_id'=> $year->id,
                'type'            => 'inscription',
                'concept'         => 'Inscripción 2025-2026',
                'amount'          => $inscAmount,
                'paid'            => $inscAmount,
                'status'          => 'paid',
                'due_date'        => '2025-08-15',
                'paid_date'       => '2025-08-' . rand(10, 30),
                'method'          => ['cash', 'transfer', 'card'][rand(0, 2)],
                'recorded_by'     => $admin?->id,
            ]);
            $payCount++;

            // Mensualidades
            $monthlyAmount = [2500, 3000, 3500][rand(0, 2)];
            foreach ($months as $mi => $month) {
                $dueDate = Carbon::parse('2025-09-01')->addMonths($mi)->startOfMonth()->addDays(4);
                if ($dueDate->gt(now())) {
                    // Futuras: pendiente
                    StudentPayment::create([
                        'student_id' => $student->id, 'academic_year_id' => $year->id,
                        'type' => 'monthly', 'concept' => "Mensualidad $month",
                        'amount' => $monthlyAmount, 'paid' => 0,
                        'status' => 'pending', 'due_date' => $dueDate,
                        'recorded_by' => $admin?->id,
                    ]);
                } else {
                    $paid = rand(0, 100) > 10;
                    StudentPayment::create([
                        'student_id' => $student->id, 'academic_year_id' => $year->id,
                        'type' => 'monthly', 'concept' => "Mensualidad $month",
                        'amount' => $monthlyAmount, 'paid' => $paid ? $monthlyAmount : 0,
                        'status' => $paid ? 'paid' : 'pending',
                        'due_date' => $dueDate,
                        'paid_date' => $paid ? $dueDate->copy()->addDays(rand(0, 5)) : null,
                        'method' => $paid ? ['cash', 'transfer', 'card'][rand(0, 2)] : null,
                        'recorded_by' => $admin?->id,
                    ]);
                }
                $payCount++;
            }
        }
        $this->command->info("  ✅ $payCount Registros de pagos");

        // ─── ORIENTACIÓN ─────────────────────────────────
        $orientTypes = ['interview', 'observation', 'referral', 'followup', 'psychological', 'family', 'academic'];
        $orientPriorities = ['urgent', 'high', 'medium', 'low'];
        $orientStatuses = ['open', 'in_progress', 'resolved', 'closed'];
        $orientCount = 0;
        foreach ($students as $student) {
            if (rand(1, 100) > 90) { // 10% tienen caso
                OrientationRecord::create([
                    'student_id'      => $student->id,
                    'academic_year_id'=> $year->id,
                    'type'            => $orientTypes[rand(0, count($orientTypes) - 1)],
                    'title'           => ['Dificultad académica', 'Problema conductual', 'Situación familiar', 'Bullying reportado', 'Ansiedad', 'Bajo rendimiento'][rand(0, 5)],
                    'description'     => 'Caso registrado para seguimiento por el departamento de orientación.',
                    'findings'        => rand(0, 1) ? 'Se identificaron factores que requieren atención.' : null,
                    'recommendations' => rand(0, 1) ? 'Seguimiento semanal con consejero asignado.' : null,
                    'priority'        => $orientPriorities[rand(0, 3)],
                    'status'          => $orientStatuses[rand(0, 3)],
                    'next_followup'   => rand(0, 1) ? now()->addDays(rand(1, 30)) : null,
                    'counselor_id'    => $admin?->id,
                    'is_confidential' => rand(0, 1),
                ]);
                $orientCount++;
            }
        }
        $this->command->info("  ✅ $orientCount Casos de orientación");

        // ─── COMUNICACIONES ──────────────────────────────
        $commData = [
            ['Reunión de Padres', 'Se convoca a reunión de padres para el próximo viernes.', 'whatsapp', 'all'],
            ['Cambio de Horario', 'Informamos que se modifica el horario de clases a partir del lunes.', 'email', 'all'],
            ['Pago Pendiente', 'Le recordamos que tiene pagos pendientes de mensualidad.', 'sms', 'individual'],
            ['Acto de Navidad', 'Les invitamos al acto navideño el 20 de diciembre.', 'whatsapp', 'all'],
            ['Suspensión por Lluvia', 'Se suspenden las clases mañana por pronóstico de lluvias.', 'internal', 'all'],
        ];
        foreach ($commData as [$subject, $body, $channel, $type]) {
            CommunicationLog::create([
                'channel'          => $channel,
                'type'             => $type,
                'subject'          => $subject,
                'body'             => $body,
                'sent_by'          => $admin?->id,
                'recipients_count' => rand(20, 300),
                'status'           => 'sent',
                'sent_at'          => now()->subDays(rand(1, 60)),
            ]);
        }
        $this->command->info('  ✅ 5 Comunicaciones');

        // ─── CIRCULARES ──────────────────────────────────
        $annData = [
            ['Inicio de Clases', 'Informamos que el inicio del año escolar será el 1 de septiembre.', 'circular', 'high'],
            ['Día de la Bandera', 'Celebraremos el Día de la Bandera con actos cívicos.', 'event', 'normal'],
            ['Exámenes Parciales', 'Los exámenes del 2do período inician el 25 de noviembre.', 'announcement', 'high'],
            ['Jornada de Vacunación', 'Se realizará jornada de vacunación en coordinación con Salud Pública.', 'alert', 'urgent'],
        ];
        foreach ($annData as [$title, $body, $type, $priority]) {
            SchoolAnnouncement::create([
                'academic_year_id' => $year->id,
                'author_id'        => $admin?->id,
                'title'            => $title,
                'body'             => $body,
                'type'             => $type,
                'priority'         => $priority,
                'audience'         => 'all',
                'publish_date'     => now()->subDays(rand(1, 90)),
                'is_published'     => true,
                'requires_acknowledgment' => rand(0, 1),
            ]);
        }
        $this->command->info('  ✅ 4 Circulares/Anuncios');

        // ─── CALENDARIO ESCOLAR ──────────────────────────
        $calData = [
            ['2025-09-01', 'school_day',  'Inicio de Clases'],
            ['2025-09-24', 'holiday',     'Día de las Mercedes'],
            ['2025-11-06', 'holiday',     'Día de la Constitución'],
            ['2025-12-20', 'event',       'Acto de Navidad'],
            ['2025-12-23', 'vacation',    'Inicio Vacaciones Navidad'],
            ['2026-01-06', 'school_day',  'Regreso a Clases'],
            ['2026-01-21', 'holiday',     'Día de la Altagracia'],
            ['2026-01-26', 'holiday',     'Día de Duarte'],
            ['2026-02-27', 'holiday',     'Día de la Independencia'],
            ['2026-03-30', 'vacation',    'Semana Santa (inicio)'],
            ['2026-04-06', 'school_day',  'Regreso Semana Santa'],
            ['2026-05-01', 'holiday',     'Día del Trabajo'],
            ['2026-06-16', 'event',       'Acto de Graduación'],
            ['2026-06-30', 'school_day',  'Último Día Lectivo'],
        ];
        foreach ($calData as [$date, $type, $name]) {
            SchoolCalendar::create([
                'academic_year_id'  => $year->id,
                'date'              => $date,
                'type'              => $type,
                'name'              => $name,
                'affects_attendance'=> in_array($type, ['holiday', 'vacation']),
            ]);
        }
        $this->command->info('  ✅ 14 Eventos de calendario');

        // ─── ALERTAS ─────────────────────────────────────
        $alertStudents = array_slice($students, 0, min(8, count($students)));
        foreach ($alertStudents as $student) {
            SchoolAlert::create([
                'student_id'      => $student->id,
                'academic_year_id'=> $year->id,
                'type'            => ['academic', 'attendance', 'behavior', 'payment'][rand(0, 3)],
                'severity'        => ['low', 'medium', 'high', 'critical'][rand(0, 3)],
                'title'           => ['Bajo rendimiento detectado', 'Ausencias frecuentes', 'Pago atrasado', 'Incidente conductual'][rand(0, 3)],
                'description'     => 'Alerta generada automáticamente por el sistema.',
                'is_resolved'     => rand(0, 1),
                'generated_by'    => $admin?->id,
            ]);
        }
        $this->command->info('  ✅ ' . count($alertStudents) . ' Alertas');

        // ─── AUDITORÍA ───────────────────────────────────
        $auditActions = ['created', 'updated', 'login', 'exported', 'approved'];
        for ($i = 0; $i < 20; $i++) {
            AuditLog::create([
                'user_id'     => $admin?->id ?? $teachers[rand(0, count($teachers) - 1)]->id,
                'action'      => $auditActions[rand(0, count($auditActions) - 1)],
                'model_type'  => ['App\\Models\\Student', 'App\\Models\\StudentGrade', 'App\\Models\\StudentPayment', 'App\\Models\\Section'][rand(0, 3)],
                'model_id'    => rand(1, 50),
                'description' => ['Estudiante creado', 'Nota registrada', 'Pago procesado', 'Sección actualizada', 'Reporte exportado', 'Sesión iniciada'][rand(0, 5)],
                'ip_address'  => '192.168.1.' . rand(1, 254),
            ]);
        }
        $this->command->info('  ✅ 20 Registros de auditoría');

        // ─── USUARIO REGISTRO (extra) ────────────────────
        $regUser = User::create([
            'name' => 'Encargada Registro', 'email' => 'registro@colegio.edu.do',
            'password' => bcrypt('Password'),
        ]);
        $regUser->assignRole('Registro');
        $this->command->info('  ✅ Usuario Registro (registro@colegio.edu.do / Password)');

        $this->command->newLine();
        $this->command->info('🎉 ¡Datos de demostración creados exitosamente!');
        $this->command->info("   📊 Resumen: {$studentIdx} estudiantes, " . count($sections) . " secciones, $gradeCount notas, $attendanceCount asistencias, $payCount pagos");
    }
}
