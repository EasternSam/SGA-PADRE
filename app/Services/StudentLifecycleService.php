<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\PromotionRecord;
use App\Models\SchoolEnrollment;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;

class StudentLifecycleService
{
    /**
     * Calcula el ciclo de vida completo del estudiante.
     * Devuelve un array de etapas con su estado y porcentaje.
     */
    public function calculate(Student $student): array
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        $enrollment = $activeYear
            ? SchoolEnrollment::where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id)
                ->first()
            : null;

        $stages = [
            $this->stageAdmission($student),
            $this->stageEnrollment($student, $enrollment),
            $this->stageDocuments($enrollment),
            $this->stageAttendance($student, $activeYear),
            $this->stageGrades($student, $activeYear),
            $this->stagePromotion($student, $activeYear),
            $this->stageGraduation($student),
        ];

        // Calcular progreso general
        $completedCount = collect($stages)->where('status', 'completed')->count();
        $totalStages = count($stages);
        $overallProgress = $totalStages > 0 ? round(($completedCount / $totalStages) * 100) : 0;

        return [
            'stages' => $stages,
            'overall_progress' => $overallProgress,
            'completed_count' => $completedCount,
            'total_stages' => $totalStages,
            'current_stage' => $this->getCurrentStage($stages),
        ];
    }

    /**
     * Calcula la historia completa multi-año del estudiante.
     */
    public function history(Student $student): array
    {
        $enrollments = SchoolEnrollment::where('student_id', $student->id)
            ->with(['academicYear', 'gradeLevel', 'section'])
            ->orderBy('created_at')
            ->get();

        $promotions = PromotionRecord::where('student_id', $student->id)
            ->with(['academicYear', 'gradeLevel'])
            ->orderBy('decision_date')
            ->get();

        $timeline = [];

        foreach ($enrollments as $en) {
            $year = $en->academicYear;
            $promo = $promotions->firstWhere('academic_year_id', $en->academic_year_id);

            $timeline[] = [
                'year_name' => $year?->name ?? 'Sin año',
                'grade' => $en->gradeLevel?->name ?? '—',
                'section' => $en->section?->name ?? '—',
                'status' => $en->status,
                'status_label' => SchoolEnrollment::STATUSES[$en->status] ?? $en->status,
                'enrollment_date' => $en->enrollment_date?->format('d/m/Y'),
                'promotion_result' => $promo?->result,
                'promotion_label' => $promo ? (PromotionRecord::RESULTS[$promo->result] ?? $promo->result) : null,
                'average' => $promo?->final_average,
                'is_current' => $year?->status === 'active',
            ];
        }

        return $timeline;
    }

    // ── Etapas Individuales ─────────────────────────────────

    private function stageAdmission(Student $student): array
    {
        $admission = Admission::where('email', $student->email)
            ->orWhere('identification_id', $student->cedula)
            ->first();

        if ($admission && !in_array($admission->status, ['rejected', 'Rechazado'])) {
            return $this->stage('Solicitud', 'Solicitud de admisión registrada', 'completed', 100, 'clipboard-document-list', [
                'Fecha' => $admission->created_at?->format('d/m/Y'),
                'Estado' => ucfirst($admission->status ?? 'Aprobada'),
            ]);
        }

        // Si no hay admisión pero ya es estudiante, se considera completada implícitamente
        if ($student->id) {
            return $this->stage('Solicitud', 'Ingreso directo sin solicitud formal', 'completed', 100, 'clipboard-document-list', [
                'Tipo' => 'Ingreso directo',
            ]);
        }

        return $this->stage('Solicitud', 'Pendiente de solicitud de admisión', 'pending', 0, 'clipboard-document-list');
    }

    private function stageEnrollment(Student $student, ?SchoolEnrollment $enrollment): array
    {
        if (!$enrollment) {
            return $this->stage('Inscripción', 'No tiene inscripción para el año activo', 'warning', 0, 'academic-cap');
        }

        $statusMap = [
            'enrolled' => ['Matriculado oficialmente', 'completed', 100],
            'approved' => ['Inscripción aprobada, pendiente de matricular', 'in_progress', 75],
            'pending'  => ['Inscripción pendiente de aprobación', 'in_progress', 30],
        ];

        $info = $statusMap[$enrollment->status] ?? ['Estado: ' . $enrollment->status, 'in_progress', 50];

        return $this->stage('Inscripción', $info[0], $info[1], $info[2], 'academic-cap', [
            'Código' => $enrollment->enrollment_code ?? '—',
            'Tipo' => SchoolEnrollment::ENROLLMENT_TYPES[$enrollment->enrollment_type] ?? $enrollment->enrollment_type,
            'Grado' => $enrollment->gradeLevel?->name ?? '—',
            'Sección' => $enrollment->section?->name ?? 'Sin asignar',
        ]);
    }

    private function stageDocuments(?SchoolEnrollment $enrollment): array
    {
        if (!$enrollment) {
            return $this->stage('Documentos', 'Requiere inscripción primero', 'pending', 0, 'folder-open');
        }

        $total = $enrollment->documents_total;
        $completed = $enrollment->documents_completed;
        $percent = $enrollment->documents_percentage;

        $missing = [];
        foreach (SchoolEnrollment::REQUIRED_DOCS as $field => $label) {
            if (!$enrollment->{$field}) {
                $missing[] = $label;
            }
        }

        $status = $percent >= 100 ? 'completed' : ($percent >= 50 ? 'in_progress' : 'warning');
        $description = $percent >= 100
            ? 'Todos los documentos han sido entregados'
            : "{$completed}/{$total} documentos entregados";

        $details = ['Entregados' => "{$completed}/{$total} ({$percent}%)"];
        if (!empty($missing)) {
            $details['Faltantes'] = implode(', ', array_slice($missing, 0, 3));
            if (count($missing) > 3) {
                $details['Faltantes'] .= ' +' . (count($missing) - 3) . ' más';
            }
        }

        return $this->stage('Documentos', $description, $status, (int) $percent, 'folder-open', $details);
    }

    private function stageAttendance(Student $student, ?AcademicYear $year): array
    {
        if (!$year) {
            return $this->stage('Asistencia', 'Sin año escolar activo', 'pending', 0, 'clock');
        }

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereDate('date', '>=', $year->start_date)
            ->get();

        $total = $records->count();

        if ($total === 0) {
            return $this->stage('Asistencia', 'Sin registros de asistencia aún', 'pending', 0, 'clock');
        }

        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();
        $rate = round((($present + $excused + $late) / $total) * 100, 1);

        $status = $rate >= 80 ? 'completed' : ($rate >= 60 ? 'warning' : 'danger');
        $description = "Tasa de asistencia: {$rate}%";

        return $this->stage('Asistencia', $description, $status, min((int)$rate, 100), 'clock', [
            'Presentes' => $present,
            'Ausencias' => $absent,
            'Tardanzas' => $late,
            'Excusadas' => $excused,
            'Total días' => $total,
        ]);
    }

    private function stageGrades(Student $student, ?AcademicYear $year): array
    {
        if (!$year || !$student->section_id) {
            return $this->stage('Calificaciones', 'Requiere sección asignada', 'pending', 0, 'chart-bar');
        }

        // Cuántos períodos hay y cuántos tienen notas completas
        $periods = EvaluationPeriod::where('academic_year_id', $year->id)->get();
        $totalPeriods = $periods->count();

        if ($totalPeriods === 0) {
            return $this->stage('Calificaciones', 'Sin períodos de evaluación configurados', 'pending', 0, 'chart-bar');
        }

        $totalSubjects = SectionSubject::where('section_id', $student->section_id)->count();
        $completedPeriods = 0;

        foreach ($periods as $period) {
            $gradesInPeriod = StudentGrade::where('student_id', $student->id)
                ->where('evaluation_period_id', $period->id)
                ->whereNotNull('score')
                ->count();

            if ($totalSubjects > 0 && $gradesInPeriod >= $totalSubjects) {
                $completedPeriods++;
            }
        }

        $percent = $totalPeriods > 0 ? round(($completedPeriods / $totalPeriods) * 100) : 0;
        $status = $percent >= 100 ? 'completed' : ($completedPeriods > 0 ? 'in_progress' : 'pending');

        // Promedio general
        $allGrades = StudentGrade::where('student_id', $student->id)
            ->whereIn('evaluation_period_id', $periods->pluck('id'))
            ->whereNotNull('score')
            ->pluck('score');
        $avg = $allGrades->count() > 0 ? round($allGrades->avg(), 1) : null;

        return $this->stage('Calificaciones', "{$completedPeriods}/{$totalPeriods} períodos completados", $status, $percent, 'chart-bar', [
            'Períodos completados' => "{$completedPeriods}/{$totalPeriods}",
            'Asignaturas' => $totalSubjects,
            'Promedio general' => $avg ?? '—',
        ]);
    }

    private function stagePromotion(Student $student, ?AcademicYear $year): array
    {
        if (!$year) {
            return $this->stage('Promoción', 'Sin año escolar activo', 'pending', 0, 'arrow-trending-up');
        }

        $promo = PromotionRecord::where('student_id', $student->id)
            ->where('academic_year_id', $year->id)
            ->first();

        if (!$promo) {
            return $this->stage('Promoción', 'Pendiente — se evalúa al cierre del año', 'pending', 0, 'arrow-trending-up');
        }

        $resultLabel = PromotionRecord::RESULTS[$promo->result] ?? $promo->result;

        $status = match ($promo->result) {
            'promoted', 'graduated' => 'completed',
            'retained' => 'danger',
            default => 'in_progress',
        };

        return $this->stage('Promoción', "Resultado: {$resultLabel}", $status, $status === 'completed' ? 100 : 50, 'arrow-trending-up', [
            'Resultado' => $resultLabel,
            'Promedio final' => $promo->final_average ?? '—',
            'Fecha' => $promo->decision_date?->format('d/m/Y') ?? '—',
            'Observaciones' => $promo->observations ?? '—',
        ]);
    }

    private function stageGraduation(Student $student): array
    {
        // Buscar si tiene un PromotionRecord con result = 'graduated'
        $graduated = PromotionRecord::where('student_id', $student->id)
            ->where('result', 'graduated')
            ->with(['academicYear', 'gradeLevel'])
            ->first();

        if ($graduated) {
            return $this->stage('Graduación', '¡Estudiante graduado!', 'completed', 100, 'trophy', [
                'Año' => $graduated->academicYear?->name ?? '—',
                'Último grado' => $graduated->gradeLevel?->name ?? '—',
                'Promedio final' => $graduated->final_average ?? '—',
                'Fecha' => $graduated->decision_date?->format('d/m/Y') ?? '—',
            ]);
        }

        // También verificar si la inscripción tiene status graduated
        $enrollGrad = SchoolEnrollment::where('student_id', $student->id)
            ->where('status', 'graduated')
            ->first();

        if ($enrollGrad) {
            return $this->stage('Graduación', '¡Estudiante egresado!', 'completed', 100, 'trophy', [
                'Año escolar' => $enrollGrad->academicYear?->name ?? '—',
            ]);
        }

        return $this->stage('Graduación', 'Meta final — se completa al terminar el último grado', 'pending', 0, 'trophy');
    }

    // ── Helpers ──────────────────────────────────────────────

    private function stage(string $name, string $description, string $status, int $percent, string $icon, array $details = []): array
    {
        return [
            'name' => $name,
            'description' => $description,
            'status' => $status,       // completed, in_progress, warning, danger, pending
            'percent' => $percent,
            'icon' => $icon,
            'details' => $details,
        ];
    }

    private function getCurrentStage(array $stages): ?string
    {
        foreach ($stages as $stage) {
            if (in_array($stage['status'], ['in_progress', 'warning', 'danger'])) {
                return $stage['name'];
            }
            if ($stage['status'] === 'pending') {
                return $stage['name'];
            }
        }
        return 'Graduación';
    }
}
