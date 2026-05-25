<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use App\Models\StudentPayment;
use Illuminate\Http\Response;

class ExcelExportController extends Controller
{
    /**
     * Export student list as CSV
     */
    public function studentList(Section $section)
    {
        $students = Student::where('section_id', $section->id)
            ->where('status', 'Activo')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $csv = $this->buildCsv(
            ['#', 'Apellido', 'Nombre', 'Matrícula', 'Sexo', 'Fec. Nacimiento', 'Edad', 'Teléfono', 'Email'],
            $students->map(fn($s, $i) => [
                $i + 1,
                $s->last_name,
                $s->first_name,
                $s->student_id ?? '',
                $s->gender ?? '',
                $s->birth_date ? \Carbon\Carbon::parse($s->birth_date)->format('d/m/Y') : '',
                $s->birth_date ? \Carbon\Carbon::parse($s->birth_date)->age : '',
                $s->phone ?? '',
                $s->email ?? '',
            ])->toArray()
        );

        return $this->csvResponse($csv, 'Lista_' . ($section->gradeLevel?->short_name ?? '') . '_' . $section->name);
    }

    /**
     * Export grades as CSV
     */
    public function grades(Section $section, EvaluationPeriod $period)
    {
        $students = Student::where('section_id', $section->id)
            ->where('status', 'Activo')
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        $sectionSubjects = SectionSubject::where('section_id', $section->id)
            ->with('subject')->get();

        $headers = ['#', 'Apellido', 'Nombre'];
        foreach ($sectionSubjects as $ss) {
            $headers[] = $ss->subject?->name ?? '—';
        }
        $headers[] = 'Promedio';

        $rows = [];
        foreach ($students as $i => $student) {
            $row = [$i + 1, $student->last_name, $student->first_name];
            $total = 0;
            $count = 0;

            foreach ($sectionSubjects as $ss) {
                $grade = StudentGrade::where('student_id', $student->id)
                    ->where('section_subject_id', $ss->id)
                    ->where('evaluation_period_id', $period->id)
                    ->first();
                $score = $grade?->score;
                $row[] = $score ?? '';
                if ($score !== null) { $total += $score; $count++; }
            }

            $row[] = $count > 0 ? round($total / $count, 1) : '';
            $rows[] = $row;
        }

        return $this->csvResponse(
            $this->buildCsv($headers, $rows),
            'Notas_' . ($section->gradeLevel?->short_name ?? '') . '_' . $section->name . '_' . $period->name
        );
    }

    /**
     * Export attendance as CSV
     */
    public function attendance(Section $section)
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $students = Student::where('section_id', $section->id)
            ->where('status', 'Activo')
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        $headers = ['#', 'Apellido', 'Nombre', 'Presencias', 'Ausencias', 'Tardanzas', 'Excusas', 'Total', '% Asist.'];
        $rows = [];

        foreach ($students as $i => $s) {
            $att = StudentAttendance::where('student_id', $s->id)
                ->when($activeYear, fn($q) => $q->whereDate('date', '>=', $activeYear->start_date))
                ->get();

            $present = $att->where('status', 'present')->count();
            $absent = $att->where('status', 'absent')->count();
            $late = $att->where('status', 'late')->count();
            $excused = $att->where('status', 'excused')->count();
            $total = $att->count();

            $rows[] = [
                $i + 1, $s->last_name, $s->first_name,
                $present, $absent, $late, $excused, $total,
                $total > 0 ? round(($present / $total) * 100, 1) . '%' : '—',
            ];
        }

        return $this->csvResponse(
            $this->buildCsv($headers, $rows),
            'Asistencia_' . ($section->gradeLevel?->short_name ?? '') . '_' . $section->name
        );
    }

    /**
     * Export payments as CSV
     */
    public function payments()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $payments = StudentPayment::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->with('student')
            ->orderByDesc('created_at')
            ->get();

        $headers = ['Estudiante', 'Tipo', 'Concepto', 'Monto', 'Pagado', 'Balance', 'Estado', 'Vence', 'Recibo'];
        $rows = $payments->map(fn($p) => [
            $p->student?->full_name ?? '—',
            StudentPayment::TYPES[$p->type] ?? $p->type,
            $p->concept,
            $p->amount,
            $p->paid,
            $p->amount - $p->paid,
            $p->status,
            $p->due_date?->format('d/m/Y') ?? '',
            $p->receipt_number ?? '',
        ])->toArray();

        return $this->csvResponse($this->buildCsv($headers, $rows), 'Pagos_' . ($activeYear?->name ?? ''));
    }

    // ------- Helpers -------

    private function buildCsv(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');
        // BOM for Excel UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    private function csvResponse(string $csv, string $filename): Response
    {
        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }
}
