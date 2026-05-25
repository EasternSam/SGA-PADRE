<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\AcademicYear;
use App\Models\SchoolConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportPdfController extends Controller
{
    /**
     * Reporte de asistencia por sección y rango de fechas.
     */
    public function sectionReport(Request $request, Section $section)
    {
        $section->load('gradeLevel');
        $dateFrom = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('to', now()->format('Y-m-d'));

        $students = Student::where('section_id', $section->id)
            ->where('status', 'Activo')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Build attendance matrix: student -> date -> status
        $dates = [];
        $current = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        while ($current <= $end) {
            if (!$current->isWeekend()) {
                $dates[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        $attendanceData = StudentAttendance::where('section_id', $section->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get()
            ->groupBy('student_id');

        $matrix = [];
        $summaries = [];
        foreach ($students as $student) {
            $records = $attendanceData[$student->id] ?? collect();
            $row = [];
            $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'total' => 0];
            
            foreach ($dates as $date) {
                $record = $records->firstWhere('date', Carbon::parse($date));
                $status = $record?->status ?? null;
                $row[$date] = $status;
                
                if ($status) {
                    $summary['total']++;
                    if (isset($summary[$status])) $summary[$status]++;
                }
            }
            
            $matrix[$student->id] = $row;
            $summaries[$student->id] = $summary;
        }

        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();

        $data = compact('section', 'students', 'dates', 'matrix', 'summaries', 'schoolConfig', 'activeYear', 'dateFrom', 'dateTo');

        $pdf = Pdf::loadView('reports.attendance-section-pdf', $data);
        $pdf->setPaper('legal', 'landscape');

        return $pdf->stream('Asistencia_' . $section->gradeLevel?->short_name . '_' . $section->name . '.pdf');
    }

    /**
     * Reporte individual de asistencia de un estudiante.
     */
    public function studentReport(Student $student)
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        
        $records = StudentAttendance::where('student_id', $student->id)
            ->when($activeYear, function($q) use ($activeYear) {
                $q->whereDate('date', '>=', $activeYear->start_date)
                  ->whereDate('date', '<=', $activeYear->end_date ?? now());
            })
            ->orderBy('date')
            ->get();

        $monthlySummary = $records->groupBy(fn($r) => $r->date->format('Y-m'))
            ->map(function($month) {
                return [
                    'present'  => $month->where('status', 'present')->count(),
                    'absent'   => $month->where('status', 'absent')->count(),
                    'late'     => $month->where('status', 'late')->count(),
                    'excused'  => $month->where('status', 'excused')->count(),
                    'total'    => $month->count(),
                ];
            });

        $overall = [
            'present' => $records->where('status', 'present')->count(),
            'absent'  => $records->where('status', 'absent')->count(),
            'late'    => $records->where('status', 'late')->count(),
            'excused' => $records->where('status', 'excused')->count(),
            'total'   => $records->count(),
        ];

        $schoolConfig = SchoolConfig::current();
        $section = Section::with('gradeLevel')->find($student->section_id);

        $data = compact('student', 'records', 'monthlySummary', 'overall', 'schoolConfig', 'activeYear', 'section');

        $pdf = Pdf::loadView('reports.attendance-student-pdf', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Asistencia_' . $student->last_name . '_' . $student->first_name . '.pdf');
    }
}
