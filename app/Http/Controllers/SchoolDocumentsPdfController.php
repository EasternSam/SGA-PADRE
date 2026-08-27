<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\DisciplineRecord;
use App\Models\EvaluationPeriod;
use App\Models\PromotionRecord;
use App\Models\SchoolConfig;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use Barryvdh\DomPDF\Facade\Pdf;

class SchoolDocumentsPdfController extends Controller
{
    /**
     * Constancia de Estudios
     */
    public function constanciaEstudios(Student $student)
    {
        $student->load('gradeLevel');
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();
        $section = Section::with('gradeLevel')->find($student->section_id);

        $data = compact('student', 'schoolConfig', 'activeYear', 'section');

        $pdf = Pdf::loadView('reports.documents.constancia-estudios', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Constancia_' . $student->last_name . '_' . $student->first_name . '.pdf');
    }

    /**
     * Carta de Buena Conducta
     */
    public function cartaConducta(Student $student)
    {
        $student->load('gradeLevel');
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();
        $section = Section::with('gradeLevel')->find($student->section_id);

        // Check discipline records
        $incidencias = DisciplineRecord::where('student_id', $student->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();

        $data = compact('student', 'schoolConfig', 'activeYear', 'section', 'incidencias');

        $pdf = Pdf::loadView('reports.documents.carta-conducta', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Conducta_' . $student->last_name . '_' . $student->first_name . '.pdf');
    }

    /**
     * Récord de Notas Oficial (todos los períodos de un año o acumulativo)
     */
    public function recordNotas(Student $student)
    {
        $student->load('gradeLevel');
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();
        $section = Section::with('gradeLevel')->find($student->section_id);

        // Get all years with grades
        $years = AcademicYear::orderBy('start_date')->get();
        $academicHistory = [];

        foreach ($years as $year) {
            $periods = EvaluationPeriod::where('academic_year_id', $year->id)->orderBy('number')->get();
            $yearGrades = [];
            $hasData = false;

            foreach ($periods as $period) {
                $grades = StudentGrade::where('student_id', $student->id)
                    ->where('evaluation_period_id', $period->id)
                    ->with('sectionSubject.subject')
                    ->get();

                if ($grades->isNotEmpty()) {
                    $hasData = true;
                    $yearGrades[] = [
                        'period' => $period,
                        'grades' => $grades,
                        'avg'    => round($grades->whereNotNull('score')->avg('score'), 1),
                    ];
                }
            }

            if ($hasData) {
                $promotion = PromotionRecord::where('student_id', $student->id)
                    ->where('academic_year_id', $year->id)
                    ->first();

                $academicHistory[] = [
                    'year'      => $year,
                    'periods'   => $yearGrades,
                    'promotion' => $promotion,
                ];
            }
        }

        $data = compact('student', 'schoolConfig', 'activeYear', 'section', 'academicHistory');

        $pdf = Pdf::loadView('reports.documents.record-notas', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Record_' . $student->last_name . '_' . $student->first_name . '.pdf');
    }

    /**
     * Certificado de Estudios
     */
    public function certificadoEstudios(Student $student)
    {
        $student->load('gradeLevel');
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();
        $section = Section::with('gradeLevel')->find($student->section_id);

        // Final average
        $allGrades = StudentGrade::where('student_id', $student->id)
            ->whereHas('evaluationPeriod', fn($q) => $q->where('academic_year_id', $activeYear?->id))
            ->whereNotNull('score')
            ->get();
        $finalAvg = $allGrades->count() > 0 ? round($allGrades->avg('score'), 2) : null;

        $promotion = PromotionRecord::where('student_id', $student->id)
            ->where('academic_year_id', $activeYear?->id)
            ->first();

        $data = compact('student', 'schoolConfig', 'activeYear', 'section', 'finalAvg', 'promotion');

        $pdf = Pdf::loadView('reports.documents.certificado-estudios', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Certificado_' . $student->last_name . '_' . $student->first_name . '.pdf');
    }

    /**
     * Ficha de Inscripción PDF
     */
    public function fichaInscripcion(Student $student)
    {
        $student->load(['gradeLevel']);
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();
        $section = Section::with('gradeLevel')->find($student->section_id);

        // Guardian
        $guardians = $student->guardians ?? collect();
        if (method_exists($student, 'guardians')) {
            // check if relationship exists
        } else {
            $guardians = \App\Models\Guardian::whereHas('students', fn($q) => $q->where('student_id', $student->id))->get();
        }

        $data = compact('student', 'schoolConfig', 'activeYear', 'section', 'guardians');

        $pdf = Pdf::loadView('reports.documents.ficha-inscripcion', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Ficha_' . $student->last_name . '_' . $student->first_name . '.pdf');
    }

    /**
     * Lista de Clase PDF
     */
    public function listaClase(Section $section)
    {
        $section->load('gradeLevel');
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();

        $students = Student::where('section_id', $section->id)
            ->where('status', 'Activo')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Attendance summary per student
        $attendanceSummary = [];
        foreach ($students as $student) {
            $total = StudentAttendance::where('student_id', $student->id)
                ->when($activeYear, fn($q) => $q->whereDate('date', '>=', $activeYear->start_date))
                ->count();
            $present = StudentAttendance::where('student_id', $student->id)
                ->when($activeYear, fn($q) => $q->whereDate('date', '>=', $activeYear->start_date))
                ->where('status', 'present')->count();

            $attendanceSummary[$student->id] = [
                'total'   => $total,
                'present' => $present,
                'pct'     => $total > 0 ? round(($present / $total) * 100) : null,
            ];
        }

        $data = compact('section', 'schoolConfig', 'activeYear', 'students', 'attendanceSummary');

        $pdf = Pdf::loadView('reports.documents.lista-clase', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Lista_' . $section->gradeLevel?->short_name . '_' . $section->name . '.pdf');
    }

    /**
     * Carta de Transferencia
     */
    public function cartaTransferencia(Student $student)
    {
        $student->load('gradeLevel');
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();
        $section = Section::with('gradeLevel')->find($student->section_id);

        $allGrades = StudentGrade::where('student_id', $student->id)
            ->whereHas('evaluationPeriod', fn($q) => $q->where('academic_year_id', $activeYear?->id))
            ->whereNotNull('score')
            ->get();
        $finalAvg = $allGrades->count() > 0 ? round($allGrades->avg('score'), 2) : null;

        $data = compact('student', 'schoolConfig', 'activeYear', 'section', 'finalAvg');

        $pdf = Pdf::loadView('reports.documents.carta-transferencia', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Transferencia_' . $student->last_name . '_' . $student->first_name . '.pdf');
    }

    /**
     * Historial de Pagos PDF
     */
    public function historialPagos(Student $student)
    {
        $student->load('gradeLevel');
        $schoolConfig = SchoolConfig::current();

        $payments = \App\Models\StudentPayment::where('student_id', $student->id)
            ->with('academicYear')
            ->orderByDesc('created_at')
            ->get();

        $totalDue = $payments->sum('amount');
        $totalPaid = $payments->sum('paid');

        $data = compact('student', 'schoolConfig', 'payments', 'totalDue', 'totalPaid');

        $pdf = Pdf::loadView('reports.documents.historial-pagos', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Pagos_' . $student->last_name . '_' . $student->first_name . '.pdf');
    }

    /**
     * Boletín Final Anual (todas las calificaciones del año consolidadas)
     */
    public function boletinFinal(Student $student)
    {
        $student->load('gradeLevel');
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();
        $section = Section::with('gradeLevel')->find($student->section_id);

        $periods = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)->orderBy('number')->get()
            : collect();

        $sectionSubjects = $section
            ? \App\Models\SectionSubject::where('section_id', $section->id)->with('subject')->get()
            : collect();

        $subjectData = [];
        $periodAvgs = [];

        foreach ($sectionSubjects as $ss) {
            $entry = [
                'subject_name' => $ss->subject?->name ?? '—',
                'scores'       => [],
                'final'        => null,
            ];

            $total = 0;
            $count = 0;

            foreach ($periods as $p) {
                $grade = StudentGrade::where('student_id', $student->id)
                    ->where('section_subject_id', $ss->id)
                    ->where('evaluation_period_id', $p->id)
                    ->first();
                $score = $grade?->score;
                $entry['scores'][$p->id] = $score;
                if ($score !== null) { $total += $score; $count++; }
            }

            $entry['final'] = $count > 0 ? round($total / $count, 1) : null;
            $subjectData[] = $entry;
        }

        // Period averages
        foreach ($periods as $p) {
            $pScores = collect($subjectData)->pluck('scores')->map(fn($s) => $s[$p->id] ?? null)->filter();
            $periodAvgs[$p->id] = $pScores->count() > 0 ? round($pScores->avg(), 1) : null;
        }

        $generalAvg = collect($subjectData)->pluck('final')->filter()->count() > 0
            ? round(collect($subjectData)->pluck('final')->filter()->avg(), 1)
            : null;

        // Attendance
        $att = StudentAttendance::where('student_id', $student->id)
            ->when($activeYear, fn($q) => $q->whereDate('date', '>=', $activeYear->start_date))
            ->get();
        $attendance = [
            'present' => $att->where('status', 'present')->count(),
            'absent'  => $att->where('status', 'absent')->count(),
            'late'    => $att->where('status', 'late')->count(),
            'total'   => $att->count(),
            'pct'     => $att->count() > 0 ? round(($att->where('status', 'present')->count() / $att->count()) * 100, 1) : 0,
        ];

        $promotion = PromotionRecord::where('student_id', $student->id)
            ->where('academic_year_id', $activeYear?->id)
            ->first();

        $data = compact('student', 'schoolConfig', 'activeYear', 'section', 'periods', 'subjectData', 'periodAvgs', 'generalAvg', 'attendance', 'promotion');

        $pdf = Pdf::loadView('reports.documents.boletin-final', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('BoletinFinal_' . $student->last_name . '_' . $student->first_name . '.pdf');
    }
}
