<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolConfig;
use App\Models\SchoolSchedule;
use App\Models\Section;
use App\Models\TimeBlock;
use Barryvdh\DomPDF\Facade\Pdf;

class SchedulePdfController extends Controller
{
    /**
     * Horario de una sección en PDF.
     */
    public function sectionSchedule(Section $section)
    {
        $section->load('gradeLevel');
        $activeYear = AcademicYear::where('status', 'active')->first();
        $schoolConfig = SchoolConfig::current();

        $blocks = TimeBlock::where('academic_year_id', $activeYear?->id)
            ->active()->ordered()->get();

        $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $dayLabels = SchoolSchedule::DAYS;

        $schedules = SchoolSchedule::where('section_id', $section->id)
            ->where('academic_year_id', $activeYear?->id)
            ->with(['subject', 'teacher'])
            ->get()
            ->groupBy(fn($s) => $s->time_block_id . '_' . $s->day_of_week);

        $grid = [];
        foreach ($blocks as $block) {
            $row = [
                'name'       => $block->name,
                'time_range' => $block->time_range,
                'type'       => $block->type,
                'cells'      => [],
            ];
            foreach ($days as $day) {
                $key = $block->id . '_' . $day;
                $s = $schedules->get($key)?->first();
                $row['cells'][$day] = [
                    'subject' => $s?->subject?->name ?? '',
                    'teacher' => $s?->teacher?->name ?? '',
                    'room'    => $s?->classroom_name ?? '',
                ];
            }
            $grid[] = $row;
        }

        $data = compact('section', 'activeYear', 'schoolConfig', 'grid', 'days', 'dayLabels');

        $pdf = Pdf::loadView('reports.schedule-section-pdf', $data);
        $pdf->setPaper('legal', 'landscape');

        return $pdf->stream('Horario_' . $section->gradeLevel?->short_name . '_' . $section->name . '.pdf');
    }

    /**
     * Horario de un docente en PDF.
     */
    public function teacherSchedule($teacherId)
    {
        $teacher = \App\Models\User::findOrFail($teacherId);
        $activeYear = AcademicYear::where('status', 'active')->first();
        $schoolConfig = SchoolConfig::current();

        $blocks = TimeBlock::where('academic_year_id', $activeYear?->id)
            ->active()->ordered()->get();

        $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $dayLabels = SchoolSchedule::DAYS;

        $schedules = SchoolSchedule::where('teacher_id', $teacherId)
            ->where('academic_year_id', $activeYear?->id)
            ->with(['subject', 'section.gradeLevel'])
            ->get()
            ->groupBy(fn($s) => $s->time_block_id . '_' . $s->day_of_week);

        $grid = [];
        foreach ($blocks as $block) {
            $row = [
                'name'       => $block->name,
                'time_range' => $block->time_range,
                'type'       => $block->type,
                'cells'      => [],
            ];
            foreach ($days as $day) {
                $key = $block->id . '_' . $day;
                $s = $schedules->get($key)?->first();
                $row['cells'][$day] = [
                    'subject' => $s?->subject?->name ?? '',
                    'section' => ($s?->section?->gradeLevel?->short_name ?? '') . ' ' . ($s?->section?->name ?? ''),
                    'room'    => $s?->classroom_name ?? '',
                ];
            }
            $grid[] = $row;
        }

        $data = compact('teacher', 'activeYear', 'schoolConfig', 'grid', 'days', 'dayLabels');

        $pdf = Pdf::loadView('reports.schedule-teacher-pdf', $data);
        $pdf->setPaper('legal', 'landscape');

        return $pdf->stream('Horario_' . $teacher->name . '.pdf');
    }
}
