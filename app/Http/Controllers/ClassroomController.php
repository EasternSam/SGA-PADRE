<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\ClassroomReservation;
use App\Models\CourseSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    /**
     * Muestra la vista principal de gestión de espacios físicos y aulas.
     */
    public function index()
    {
        $buildings = Building::with(['classrooms' => function ($q) {
            $q->with(['reservations' => function ($rq) {
                $rq->where('reserved_date', '>=', now()->toDateString())
                   ->orderBy('reserved_date')
                   ->orderBy('start_time');
            }, 'schedules' => function ($sq) {
                $sq->whereNull('deleted_at')
                   ->whereDate('end_date', '>=', now()->startOfDay())
                   ->with(['module.course', 'teacher']);
            }]);
        }])->get();

        $allClassrooms = Classroom::all();
        $totalClassrooms = $allClassrooms->count();
        $totalCapacity = $allClassrooms->sum('capacity');
        $totalPcs = $allClassrooms->sum('pc_count');

        // Calcular estado de ocupación actual
        $occupiedCount = 0;
        $buildingsData = $buildings->map(function ($b) use (&$occupiedCount) {
            return [
                'id' => $b->id,
                'name' => $b->name,
                'classrooms' => $b->classrooms->map(function ($c) use (&$occupiedCount) {
                    $isOccupied = $c->isOccupiedNow();
                    if ($isOccupied) {
                        $occupiedCount++;
                    }
                    $equipment = is_string($c->equipment) ? json_decode($c->equipment, true) : ($c->equipment ?? []);

                    return [
                        'id' => $c->id,
                        'name' => $c->name,
                        'capacity' => (int) $c->capacity,
                        'pc_count' => (int) $c->pc_count,
                        'type' => $c->type,
                        'building_id' => $c->building_id,
                        'building_name' => $c->building?->name,
                        'is_active' => (bool) $c->is_active,
                        'has_tv' => is_array($equipment) && in_array('TV', $equipment),
                        'equipment' => $equipment,
                        'is_occupied' => (bool) $isOccupied,
                        'occupant_label' => $c->getCurrentOccupantLabel(),
                        'schedules_count' => $c->schedules->count(),
                        'reservations_count' => $c->reservations->count(),
                    ];
                }),
            ];
        });

        $stats = [
            'total_classrooms' => $totalClassrooms,
            'total_capacity' => $totalCapacity,
            'total_pcs' => $totalPcs,
            'occupied_count' => $occupiedCount,
            'total_buildings' => $buildings->count(),
        ];

        return Inertia::render('Academic/Classrooms/Index', [
            'buildings' => $buildingsData,
            'rawBuildings' => Building::select('id', 'name')->orderBy('name')->get(),
            'stats' => $stats,
        ]);
    }

    /**
     * Obtiene el horario y reservas específicas de un aula para inspección modal.
     */
    public function getSchedule(Classroom $classroom)
    {
        $schedules = CourseSchedule::where('classroom_id', $classroom->id)
            ->whereNull('deleted_at')
            ->whereDate('end_date', '>=', now()->startOfDay())
            ->with(['module.course', 'teacher'])
            ->orderBy('start_time')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'course_name' => $s->module?->course?->name ?? 'Materia',
                    'module_name' => $s->module?->name ?? '',
                    'teacher_name' => $s->teacher?->name ?? 'Docente no asignado',
                    'start_time' => substr($s->start_time, 0, 5),
                    'end_time' => substr($s->end_time, 0, 5),
                    'days_of_week' => is_array($s->days_of_week) ? $s->days_of_week : json_decode($s->days_of_week ?? '[]', true),
                    'start_date' => $s->start_date ? Carbon::parse($s->start_date)->format('d/m/Y') : null,
                    'end_date' => $s->end_date ? Carbon::parse($s->end_date)->format('d/m/Y') : null,
                ];
            });

        $reservations = ClassroomReservation::where('classroom_id', $classroom->id)
            ->where('reserved_date', '>=', now()->toDateString())
            ->orderBy('reserved_date')
            ->orderBy('start_time')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'title' => $r->title,
                    'description' => $r->description,
                    'reserved_date' => Carbon::parse($r->reserved_date)->format('d/m/Y'),
                    'start_time' => substr($r->start_time, 0, 5),
                    'end_time' => substr($r->end_time, 0, 5),
                    'raw_date' => $r->reserved_date,
                ];
            });

        return response()->json([
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'building_name' => $classroom->building?->name,
                'type' => $classroom->type,
                'capacity' => $classroom->capacity,
            ],
            'schedules' => $schedules,
            'reservations' => $reservations,
        ]);
    }

    /**
     * Registra un nuevo edificio.
     */
    public function storeBuilding(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:buildings,name',
        ], [
            'name.required' => 'El nombre del edificio es obligatorio.',
            'name.unique' => 'Ya existe un edificio registrado con ese nombre.',
        ]);

        Building::create($validated);

        return redirect()->back()->with('success', 'Edificio registrado correctamente.');
    }

    /**
     * Registra una nueva aula.
     */
    public function storeClassroom(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'building_id' => 'required|exists:buildings,id',
            'capacity' => 'required|integer|min:1',
            'pc_count' => 'nullable|integer|min:0',
            'type' => 'required|in:Aula,Laboratorio,Auditorio',
            'has_tv' => 'boolean',
        ]);

        $equipment = [];
        if ($request->boolean('has_tv')) {
            $equipment[] = 'TV';
        }

        Classroom::create([
            'name' => $validated['name'],
            'building_id' => $validated['building_id'],
            'capacity' => $validated['capacity'],
            'pc_count' => $validated['pc_count'] ?? 0,
            'type' => $validated['type'],
            'equipment' => !empty($equipment) ? json_encode($equipment) : null,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Aula creada exitosamente.');
    }

    /**
     * Actualiza un aula existente.
     */
    public function updateClassroom(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'building_id' => 'required|exists:buildings,id',
            'capacity' => 'required|integer|min:1',
            'pc_count' => 'nullable|integer|min:0',
            'type' => 'required|in:Aula,Laboratorio,Auditorio',
            'has_tv' => 'boolean',
        ]);

        $equipment = [];
        if ($request->boolean('has_tv')) {
            $equipment[] = 'TV';
        }

        $classroom->update([
            'name' => $validated['name'],
            'building_id' => $validated['building_id'],
            'capacity' => $validated['capacity'],
            'pc_count' => $validated['pc_count'] ?? 0,
            'type' => $validated['type'],
            'equipment' => !empty($equipment) ? json_encode($equipment) : null,
        ]);

        return redirect()->back()->with('success', 'Aula actualizada exitosamente.');
    }

    /**
     * Elimina un aula.
     */
    public function destroyClassroom(Classroom $classroom)
    {
        $classroom->delete();
        return redirect()->back()->with('success', 'Aula eliminada correctamente.');
    }

    /**
     * Crea una reserva de aula con detección estricta de conflictos.
     */
    public function storeReservation(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reserved_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ], [
            'end_time.after' => 'La hora de fin debe ser posterior a la de inicio.',
            'reserved_date.after_or_equal' => 'La fecha no puede ser en el pasado.',
        ]);

        // Verificar colisión con otras reservas
        $conflict = ClassroomReservation::where('classroom_id', $validated['classroom_id'])
            ->where('reserved_date', $validated['reserved_date'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhere(function ($q) use ($validated) {
                          $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                      });
            })
            ->exists();

        if ($conflict) {
            return redirect()->back()->withErrors([
                'start_time' => 'Ya existe una reserva especial para este horario en esta aula.',
            ]);
        }

        // Crear la reserva
        ClassroomReservation::create([
            'classroom_id' => $validated['classroom_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'reserved_date' => $validated['reserved_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'created_by' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Reserva de aula creada con éxito.');
    }

    /**
     * Elimina una reserva.
     */
    public function destroyReservation(ClassroomReservation $reservation)
    {
        $reservation->delete();
        return redirect()->back()->with('success', 'Reserva cancelada correctamente.');
    }
}
