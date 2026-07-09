<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CourseSchedule;
use App\Models\AcademicDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TeacherDocuments extends Component
{
    use WithFileUploads;

    public $name = '';
    public $file;
    public $selectedScheduleId = ''; // Sección destino
    
    public $activeSchedules = [];
    public $uploadedDocuments = [];

    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'name' => 'required|min:3|max:100',
        'file' => 'required|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,jpg,jpeg,png', // Máximo 10MB
        'selectedScheduleId' => 'required|exists:course_schedules,id',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // Cargar las secciones activas dictadas por el profesor
        $this->activeSchedules = CourseSchedule::where('teacher_id', Auth::id())
            ->where('status', 'Activo')
            ->with('module')
            ->get();

        // Cargar los documentos subidos por el profesor
        $this->uploadedDocuments = AcademicDocument::where('uploaded_by', Auth::id())
            ->with(['module', 'courseSchedule.module'])
            ->latest()
            ->get();
    }

    public function save()
    {
        $this->resetMessages();
        $this->validate();

        try {
            $schedule = CourseSchedule::find($this->selectedScheduleId);

            // Guardar el archivo en el disco público (local)
            // Esto creará una ruta como storage/app/public/academic_documents/xxx.pdf
            $fileName = time() . '_' . $this->file->getClientOriginalName();
            $path = $this->file->storeAs('academic_documents', $fileName, 'public');

            // Crear el documento
            AcademicDocument::create([
                'name' => $this->name,
                'file_path' => '/storage/' . $path,
                'file_size' => $this->file->getSize(),
                'file_type' => $this->file->getClientOriginalExtension(),
                'module_id' => $schedule->module_id,
                'course_schedule_id' => $this->selectedScheduleId,
                'uploaded_by' => Auth::id(),
            ]);

            $this->name = '';
            $this->file = null;
            $this->selectedScheduleId = '';
            
            $this->successMessage = '¡Documento subido y compartido con éxito!';
            $this->loadData();

        } catch (\Exception $e) {
            $this->errorMessage = 'Error al subir el archivo: ' . $e->getMessage();
            Log::error('DMS: Error subiendo archivo: ' . $e->getMessage());
        }
    }

    public function deleteDocument($id)
    {
        $this->resetMessages();
        $doc = AcademicDocument::where('uploaded_by', Auth::id())->find($id);

        if ($doc) {
            // Eliminar el archivo físico
            $relativePath = str_replace('/storage/', '', $doc->file_path);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }

            $doc->delete();
            $this->successMessage = 'Documento eliminado correctamente.';
            $this->loadData();
        } else {
            $this->errorMessage = 'No tienes permiso para eliminar este documento.';
        }
    }

    public function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.teacher-portal.teacher-documents')
            ->layout('layouts.dashboard');
    }
}
