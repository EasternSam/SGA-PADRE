<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\AcademicDocument;
use Illuminate\Support\Facades\Auth;

class DocumentRepository extends Component
{
    public $student;
    public $enrollments = [];
    public $documentsGrouped = []; // module_id => [documents]

    public function mount()
    {
        $this->student = Auth::user()->student;

        if (!$this->student) {
            abort(403, 'No tienes un perfil de estudiante asociado.');
        }

        $this->loadDocuments();
    }

    public function loadDocuments()
    {
        // 1. Obtener las inscripciones activas (materias cursando o prematriculadas)
        $this->enrollments = Enrollment::where('student_id', $this->student->id)
            ->whereIn('enrollments.status', ['Cursando', 'Pendiente'])
            ->with(['courseSchedule.module'])
            ->get();

        $scheduleIds = $this->enrollments->pluck('course_schedule_id')->toArray();
        $moduleIds = $this->enrollments->pluck('courseSchedule.module_id')->toArray();

        if (empty($scheduleIds)) {
            $this->documentsGrouped = [];
            return;
        }

        // 2. Obtener los documentos asociados a estas secciones o asignaturas generales
        $documents = AcademicDocument::whereIn('course_schedule_id', $scheduleIds)
            ->orWhere(function ($query) use ($moduleIds) {
                $query->whereIn('module_id', $moduleIds)->whereNull('course_schedule_id');
            })
            ->with(['module', 'uploader', 'courseSchedule'])
            ->latest()
            ->get();

        // 3. Agrupar documentos por módulo para organizarlos de manera limpia en la vista
        $this->documentsGrouped = [];
        foreach ($documents as $doc) {
            $this->documentsGrouped[$doc->module_id][] = $doc;
        }
    }

    public function render()
    {
        return view('livewire.student-portal.document-repository')
            ->layout('layouts.dashboard');
    }
}
