<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SectionController extends Controller
{
    /**
     * Muestra el directorio de secciones escolares organizadas por grado y año escolar.
     */
    public function index(Request $request)
    {
        $filterYear = $request->input('year');
        $filterLevel = $request->input('level');
        $search = trim($request->input('search', ''));

        $query = Section::with(['gradeLevel', 'academicYear', 'homeroomTeacher', 'classroom'])
            ->withCount('students');

        if (!empty($filterYear)) {
            $query->where('academic_year_id', $filterYear);
        }

        if (!empty($filterLevel)) {
            $query->where('grade_level_id', $filterLevel);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        $sections = $query->orderBy('grade_level_id')->orderBy('name')->get()->map(function ($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'full_name' => $section->full_name ?: ($section->gradeLevel?->short_name . ' ' . $section->name),
                'academic_year_id' => $section->academic_year_id,
                'academic_year_name' => $section->academicYear?->name ?? 'Sin año',
                'grade_level_id' => $section->grade_level_id,
                'grade_level_name' => $section->gradeLevel?->name ?? 'Sin grado',
                'grade_level_short' => $section->gradeLevel?->short_name ?? '',
                'homeroom_teacher_id' => $section->homeroom_teacher_id,
                'homeroom_teacher_name' => $section->homeroomTeacher?->name ?? 'Sin maestro titular',
                'classroom_id' => $section->classroom_id,
                'classroom_name' => $section->classroom?->name ?? null,
                'capacity' => (int) $section->capacity,
                'student_count' => (int) $section->students_count,
                'available_slots' => max(0, (int) $section->capacity - (int) $section->students_count),
                'is_active' => (bool) $section->is_active,
            ];
        });

        $academicYears = AcademicYear::orderByDesc('start_date')->get(['id', 'name', 'status']);
        $gradeLevels = GradeLevel::active()->ordered()->get(['id', 'name', 'short_name', 'level']);
        $teachers = User::role('Profesor')->orderBy('name')->get(['id', 'name', 'email']);
        $classrooms = Classroom::where('is_active', true)->orderBy('name')->get(['id', 'name', 'capacity', 'type']);

        $stats = [
            'total_sections' => $sections->count(),
            'total_capacity' => $sections->sum('capacity'),
            'total_enrolled' => $sections->sum('student_count'),
            'with_teacher' => $sections->whereNotNull('homeroom_teacher_id')->count(),
        ];

        return Inertia::render('Academic/Sections/Index', [
            'sections' => $sections,
            'academicYears' => $academicYears,
            'gradeLevels' => $gradeLevels,
            'teachers' => $teachers,
            'classrooms' => $classrooms,
            'filters' => [
                'year' => $filterYear,
                'level' => $filterLevel,
                'search' => $search,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Registra una nueva sección.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'name' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1|max:100',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ], [
            'academic_year_id.required' => 'Debe seleccionar un año escolar.',
            'grade_level_id.required' => 'Debe seleccionar un grado escolar.',
            'name.required' => 'El nombre o identificador de sección (Ej: A, B, C) es obligatorio.',
            'capacity.required' => 'La capacidad máxima es obligatoria.',
        ]);

        $section = Section::create([
            'academic_year_id' => $validated['academic_year_id'],
            'grade_level_id' => $validated['grade_level_id'],
            'name' => strtoupper($validated['name']),
            'capacity' => $validated['capacity'],
            'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?: null,
            'classroom_id' => $validated['classroom_id'] ?: null,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', "Sección {$section->full_name} creada con éxito.");
    }

    /**
     * Actualiza una sección existente.
     */
    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'name' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1|max:100',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $section->update([
            'academic_year_id' => $validated['academic_year_id'],
            'grade_level_id' => $validated['grade_level_id'],
            'name' => strtoupper($validated['name']),
            'capacity' => $validated['capacity'],
            'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?: null,
            'classroom_id' => $validated['classroom_id'] ?: null,
        ]);

        return redirect()->back()->with('success', "Sección {$section->full_name} actualizada correctamente.");
    }

    /**
     * Elimina una sección.
     */
    public function destroy(Section $section)
    {
        $name = $section->full_name ?: $section->name;
        $section->delete();

        return redirect()->back()->with('success', "Sección {$name} eliminada.");
    }
}
