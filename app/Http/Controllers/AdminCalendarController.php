<?php

namespace App\Http\Controllers;

use App\Models\AcademicEvent;
use App\Models\Classroom;
use App\Models\CourseSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCalendarController extends Controller
{
    private array $dayTranslations = [
        'Monday'    => 'Lunes',
        'Tuesday'   => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday'  => 'Jueves',
        'Friday'    => 'Viernes',
        'Saturday'  => 'Sábado',
        'Sunday'    => 'Domingo',
    ];

    /**
     * Muestra la interfaz moderna del Calendario Académico (Untitled UI).
     */
    public function index(Request $request): Response
    {
        $view = $request->query('view', 'month');
        if (!in_array($view, ['month', 'week', 'day'])) {
            $view = 'month';
        }

        $dateParam = $request->query('date');
        $selectedDate = $dateParam ? Carbon::parse($dateParam) : Carbon::now();

        // Filtros de Capas
        $showClasses = $request->boolean('classes', true);
        $showEvents = $request->boolean('events', true);
        $showSystem = $request->boolean('system', true);

        // Filtros específicos por Aula o Docente
        $classroomId = $request->query('classroom_id');
        $teacherId = $request->query('teacher_id');

        // Rango de fechas según la vista
        if ($view === 'month') {
            $rangeStart = $selectedDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $rangeEnd = $selectedDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        } elseif ($view === 'week') {
            $rangeStart = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
            $rangeEnd = $selectedDate->copy()->endOfWeek(Carbon::SUNDAY);
        } else {
            $rangeStart = $selectedDate->copy()->startOfDay();
            $rangeEnd = $selectedDate->copy()->endOfDay();
        }

        $allEvents = [];

        // 1. Cargar Eventos Institucionales
        if ($showEvents) {
            $academicEvents = AcademicEvent::whereBetween('date', [
                $rangeStart->format('Y-m-d'),
                $rangeEnd->format('Y-m-d')
            ])->get();

            foreach ($academicEvents as $evt) {
                $startStr = $evt->start_time ? Carbon::parse($evt->start_time)->format('H:i') : null;
                $endStr = $evt->end_time ? Carbon::parse($evt->end_time)->format('H:i') : null;

                $allEvents[] = [
                    'id'             => 'event_' . $evt->id,
                    'db_id'          => $evt->id,
                    'title'          => $evt->title,
                    'description'    => $evt->description,
                    'date'           => Carbon::parse($evt->date)->format('Y-m-d'),
                    'start_time'     => $startStr,
                    'end_time'       => $endStr,
                    'type'           => $evt->type ?? 'academic',
                    'category'       => 'event',
                    'is_all_day'     => empty($startStr),
                    'is_editable'    => true,
                ];
            }
        }

        // 2. Cargar Hitos de Inicio y Fin de Cursos / Hitos Administrativos
        if ($showSystem && empty($classroomId) && empty($teacherId)) {
            $starts = CourseSchedule::with(['module:id,name,course_id', 'module.course:id,name'])
                ->whereBetween('start_date', [$rangeStart->format('Y-m-d'), $rangeEnd->format('Y-m-d')])
                ->get();

            foreach ($starts as $s) {
                $courseName = $s->module?->course?->name ?? $s->module?->name ?? 'Curso';
                $allEvents[] = [
                    'id'          => 'system_start_' . $s->id,
                    'title'       => 'Inicio: ' . ($s->section_name ?: $courseName),
                    'description' => 'Apertura de sección ' . $s->section_name . ' (' . $courseName . ')',
                    'date'        => Carbon::parse($s->start_date)->format('Y-m-d'),
                    'start_time'  => '08:00',
                    'end_time'    => '09:00',
                    'type'        => 'start',
                    'category'    => 'system',
                    'is_all_day'  => true,
                    'is_editable' => false,
                ];
            }

            $ends = CourseSchedule::with(['module:id,name,course_id', 'module.course:id,name'])
                ->whereBetween('end_date', [$rangeStart->format('Y-m-d'), $rangeEnd->format('Y-m-d')])
                ->get();

            foreach ($ends as $e) {
                $courseName = $e->module?->course?->name ?? $e->module?->name ?? 'Curso';
                $allEvents[] = [
                    'id'          => 'system_end_' . $e->id,
                    'title'       => 'Fin: ' . ($e->section_name ?: $courseName),
                    'description' => 'Cierre y evaluación final de ' . $e->section_name . ' (' . $courseName . ')',
                    'date'        => Carbon::parse($e->end_date)->format('Y-m-d'),
                    'start_time'  => '17:00',
                    'end_time'    => '18:00',
                    'type'        => 'end',
                    'category'    => 'system',
                    'is_all_day'  => true,
                    'is_editable' => false,
                ];
            }

            // Hito administrativo del día 28 de cada mes
            $cur = $rangeStart->copy();
            while ($cur->lte($rangeEnd)) {
                if ($cur->day === 28) {
                    $allEvents[] = [
                        'id'          => 'admin_cutoff_' . $cur->format('Y-m-d'),
                        'title'       => 'Corte Administrativo Institucional',
                        'description' => 'Generación y auditoría de estados de cuenta mensuales.',
                        'date'        => $cur->format('Y-m-d'),
                        'start_time'  => '00:00',
                        'end_time'    => '23:59',
                        'type'        => 'administrative',
                        'category'    => 'system',
                        'is_all_day'  => true,
                        'is_editable' => false,
                    ];
                }
                $cur->addDay();
            }
        }

        // 3. Cargar Clases Activas por día de la semana
        if ($showClasses) {
            $scheduleQuery = CourseSchedule::with([
                'module:id,name,course_id',
                'module.course:id,name',
                'teacher:id,name',
                'classroom:id,name'
            ])
            ->where('start_date', '<=', $rangeEnd->format('Y-m-d'))
            ->where('end_date', '>=', $rangeStart->format('Y-m-d'));

            if (!empty($classroomId)) {
                $scheduleQuery->where('classroom_id', $classroomId);
            }
            if (!empty($teacherId)) {
                $scheduleQuery->where('teacher_id', $teacherId);
            }

            $activeSchedules = $scheduleQuery->get();

            $dayPointer = $rangeStart->copy();
            while ($dayPointer->lte($rangeEnd)) {
                $dayName = $this->dayTranslations[$dayPointer->englishDayOfWeek] ?? $dayPointer->dayName;
                $dateStr = $dayPointer->format('Y-m-d');

                foreach ($activeSchedules as $sch) {
                    $schStart = Carbon::parse($sch->start_date);
                    $schEnd = Carbon::parse($sch->end_date);

                    if ($dayPointer->between($schStart, $schEnd)) {
                        $days = $sch->days_of_week;
                        $matchesDay = false;
                        if (is_array($days)) {
                            $matchesDay = in_array($dayName, $days);
                        } elseif (is_string($days)) {
                            $matchesDay = str_contains($days, $dayName);
                        }

                        if ($matchesDay) {
                            $startTime = $sch->start_time ? substr($sch->start_time, 0, 5) : '08:00';
                            $endTime = $sch->end_time ? substr($sch->end_time, 0, 5) : '10:00';
                            $subjectName = $sch->module?->course?->name ?? $sch->module?->name ?? 'Clase';

                            $allEvents[] = [
                                'id'             => 'class_' . $sch->id . '_' . $dateStr,
                                'schedule_id'    => $sch->id,
                                'classroom_id'   => $sch->classroom_id,
                                'teacher_id'     => $sch->teacher_id,
                                'title'          => ($sch->section_name ? $sch->section_name . ': ' : '') . $subjectName,
                                'description'    => 'Docente: ' . ($sch->teacher?->name ?? 'Sin asignar'),
                                'date'           => $dateStr,
                                'start_time'     => $startTime,
                                'end_time'       => $endTime,
                                'type'           => 'class',
                                'category'       => 'class',
                                'classroom'      => $sch->classroom?->name ?? 'Aula general',
                                'teacher'        => $sch->teacher?->name ?? 'Por definir',
                                'modality'       => $sch->modality ?? 'Presencial',
                                'is_all_day'     => false,
                                'is_editable'    => false,
                            ];
                        }
                    }
                }

                $dayPointer->addDay();
            }
        }

        // Catálogos para filtros
        $classrooms = Classroom::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $teachers = User::whereHas('roles', fn($q) => $q->where('name', 'Profesor'))->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Calendar/Index', [
            'events'         => $allEvents,
            'currentView'    => $view,
            'currentDate'    => $selectedDate->format('Y-m-d'),
            'classrooms'     => $classrooms,
            'teachers'       => $teachers,
            'filters'        => [
                'classes'      => $showClasses,
                'events'       => $showEvents,
                'system'       => $showSystem,
                'classroom_id' => $classroomId ?? '',
                'teacher_id'   => $teacherId ?? '',
            ],
            'meta' => [
                'title'       => 'Calendario Académico',
                'description' => 'Planificación docente, hitos y actividades en vistas mensual, semanal y diaria.',
            ]
        ]);
    }

    /**
     * Guarda una nueva actividad académica o evento.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'date'        => 'required|date',
            'type'        => 'required|in:academic,administrative,holiday,extracurricular',
            'start_time'  => 'nullable|string',
            'end_time'    => 'nullable|string',
            'description' => 'nullable|string|max:1000',
        ]);

        $dateStr = Carbon::parse($validated['date'])->format('Y-m-d');
        $start = !empty($validated['start_time']) ? $dateStr . ' ' . $validated['start_time'] . ':00' : null;
        $end = !empty($validated['end_time']) ? $dateStr . ' ' . $validated['end_time'] . ':00' : null;

        AcademicEvent::create([
            'title'       => $validated['title'],
            'date'        => $dateStr,
            'type'        => $validated['type'],
            'start_time'  => $start,
            'end_time'    => $end,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Actividad agendada correctamente.');
    }

    /**
     * Actualiza un evento existente.
     */
    public function update(Request $request, AcademicEvent $event)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'date'        => 'required|date',
            'type'        => 'required|in:academic,administrative,holiday,extracurricular',
            'start_time'  => 'nullable|string',
            'end_time'    => 'nullable|string',
            'description' => 'nullable|string|max:1000',
        ]);

        $dateStr = Carbon::parse($validated['date'])->format('Y-m-d');
        $start = !empty($validated['start_time']) ? $dateStr . ' ' . $validated['start_time'] . ':00' : null;
        $end = !empty($validated['end_time']) ? $dateStr . ' ' . $validated['end_time'] . ':00' : null;

        $event->update([
            'title'       => $validated['title'],
            'date'        => $dateStr,
            'type'        => $validated['type'],
            'start_time'  => $start,
            'end_time'    => $end,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Actividad actualizada.');
    }

    /**
     * Elimina un evento académico.
     */
    public function destroy(AcademicEvent $event)
    {
        $event->delete();
        return redirect()->back()->with('success', 'Actividad eliminada.');
    }
}
