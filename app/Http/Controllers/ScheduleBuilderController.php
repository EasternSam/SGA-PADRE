<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\SchoolConfig;
use App\Models\SchoolSchedule;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TimeBlock;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleBuilderController extends Controller
{
    /**
     * Muestra la matriz interactiva del generador y constructor de horarios escolares.
     */
    public function index(Request $request)
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $sections = $activeYear
            ? Section::where('academic_year_id', $activeYear->id)
                ->with('gradeLevel')
                ->orderBy('grade_level_id')
                ->orderBy('name')
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->full_name ?: ($s->gradeLevel?->short_name . ' ' . $s->name),
                    'grade_level' => $s->gradeLevel?->name,
                ])
            : collect();

        $sectionId = $request->input('section_id');
        if (!$sectionId && $sections->isNotEmpty()) {
            $sectionId = (string) $sections->first()['id'];
        }

        $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $scheduleGrid = [];
        $blocks = collect();

        if ($activeYear) {
            $blocks = TimeBlock::where('academic_year_id', $activeYear->id)
                ->active()
                ->ordered()
                ->get();

            if ($sectionId && $blocks->isNotEmpty()) {
                $existing = SchoolSchedule::where('section_id', $sectionId)
                    ->where('academic_year_id', $activeYear->id)
                    ->with(['subject', 'teacher'])
                    ->get()
                    ->groupBy(function ($s) {
                        return $s->time_block_id . '_' . $s->day_of_week;
                    });

                foreach ($blocks as $block) {
                    $row = [
                        'block_id'   => $block->id,
                        'block_name' => $block->name,
                        'time_range' => $block->time_range,
                        'type'       => $block->type,
                        'type_label' => TimeBlock::TYPES[$block->type] ?? $block->type,
                        'cells'      => [],
                    ];

                    foreach ($days as $day) {
                        $schedule = $existing->get($block->id . '_' . $day)?->first();
                        $row['cells'][$day] = [
                            'id'             => $schedule?->id,
                            'subject_id'     => $schedule?->subject_id,
                            'subject_name'   => $schedule?->subject?->name,
                            'teacher_id'     => $schedule?->teacher_id,
                            'teacher_name'   => $schedule?->teacher?->name,
                            'classroom_name' => $schedule?->classroom_name,
                        ];
                    }

                    $scheduleGrid[] = $row;
                }
            }
        }

        $subjects = Subject::active()->orderBy('name')->get(['id', 'name', 'code']);
        $teachers = User::role('Profesor')->orderBy('name')->get(['id', 'name', 'email']);
        $classrooms = Classroom::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Academic/Schedules/Builder', [
            'activeYear' => $activeYear ? [
                'id' => $activeYear->id,
                'name' => $activeYear->name,
                'status' => $activeYear->status,
            ] : null,
            'sections' => $sections,
            'selectedSectionId' => $sectionId ? (int) $sectionId : null,
            'hasBlocks' => $blocks->isNotEmpty(),
            'scheduleGrid' => $scheduleGrid,
            'days' => $days,
            'dayLabels' => SchoolSchedule::DAYS,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Guarda o actualiza una celda horaria para la sección activa.
     */
    public function saveCell(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'time_block_id' => 'required|exists:time_blocks,id',
            'day_of_week' => 'required|in:lunes,martes,miercoles,jueves,viernes',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'nullable|exists:users,id',
            'classroom_name' => 'nullable|string|max:100',
        ]);

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) {
            return redirect()->back()->withErrors(['general' => 'No hay un año escolar activo.']);
        }

        // Si no se especifica asignatura, vaciamos la celda
        if (empty($validated['subject_id'])) {
            SchoolSchedule::where('section_id', $validated['section_id'])
                ->where('time_block_id', $validated['time_block_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->delete();

            return redirect()->back()->with('success', 'Espacio horario liberado.');
        }

        // Verificar conflicto del docente
        if (!empty($validated['teacher_id'])) {
            $conflict = SchoolSchedule::teacherConflicts(
                (int) $validated['teacher_id'],
                $validated['day_of_week'],
                (int) $validated['time_block_id']
            );

            if ($conflict && $conflict->section_id != $validated['section_id']) {
                $sectionDesc = $conflict->section?->full_name ?? "Sección #{$conflict->section_id}";
                $subjectDesc = $conflict->subject?->name ?? 'otra materia';
                return redirect()->back()->withErrors([
                    'teacher_id' => "Conflicto: El docente ya tiene asignada {$subjectDesc} en {$sectionDesc} en este mismo horario.",
                ]);
            }
        }

        SchoolSchedule::updateOrCreate(
            [
                'section_id'    => $validated['section_id'],
                'time_block_id' => $validated['time_block_id'],
                'day_of_week'   => $validated['day_of_week'],
            ],
            [
                'academic_year_id' => $activeYear->id,
                'subject_id'       => $validated['subject_id'],
                'teacher_id'       => $validated['teacher_id'] ?: null,
                'classroom_name'   => $validated['classroom_name'] ?: null,
            ]
        );

        return redirect()->back()->with('success', 'Horario actualizado exitosamente.');
    }

    /**
     * Limpia la asignación de una celda horaria.
     */
    public function clearCell(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'time_block_id' => 'required|exists:time_blocks,id',
            'day_of_week' => 'required|in:lunes,martes,miercoles,jueves,viernes',
        ]);

        SchoolSchedule::where('section_id', $validated['section_id'])
            ->where('time_block_id', $validated['time_block_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->delete();

        return redirect()->back()->with('success', 'Bloque horario vaciado.');
    }

    /**
     * Comprueba en tiempo real si el docente seleccionado genera choque horario.
     */
    public function checkConflict(Request $request)
    {
        $teacherId = $request->input('teacher_id');
        $day = $request->input('day');
        $timeBlockId = $request->input('time_block_id');
        $currentScheduleId = $request->input('schedule_id');

        if (!$teacherId || !$day || !$timeBlockId) {
            return response()->json(['has_conflict' => false]);
        }

        $conflict = SchoolSchedule::teacherConflicts(
            (int) $teacherId,
            $day,
            (int) $timeBlockId,
            $currentScheduleId ? (int) $currentScheduleId : null
        );

        if ($conflict) {
            $sectionName = $conflict->section?->full_name ?? "Sección #{$conflict->section_id}";
            $subjectName = $conflict->subject?->name ?? 'Asignatura';
            return response()->json([
                'has_conflict' => true,
                'conflict_message' => "Este docente ya está asignado en {$sectionName} ({$subjectName}).",
            ]);
        }

        return response()->json(['has_conflict' => false]);
    }

    /**
     * Genera automáticamente los bloques horarios iniciales según la tanda escolar.
     */
    public function generateBlocks(Request $request)
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) {
            return redirect()->back()->withErrors(['general' => 'No hay un año escolar activo para generar bloques.']);
        }

        $config = SchoolConfig::current();
        $shift = $config?->shift ?? 'matutina';
        $template = TimeBlock::TEMPLATES[$shift] ?? TimeBlock::TEMPLATES['matutina'];

        foreach ($template as $i => $block) {
            TimeBlock::firstOrCreate(
                [
                    'academic_year_id' => $activeYear->id,
                    'start_time'       => $block['start_time'],
                ],
                array_merge($block, [
                    'academic_year_id' => $activeYear->id,
                    'order'            => $i,
                ])
            );
        }

        return redirect()->back()->with('success', "Bloques horarios generados para la tanda {$shift}.");
    }
}
