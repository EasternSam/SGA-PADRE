<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\Storage;

class CertificateEditor extends Component
{
    use WithFileUploads;

    public $templateId;
    public $name = '';
    public $bgImage;
    public $currentBg;
    public $elements = []; 
    public $canvasConfig = [
        'width' => 1123, 
        'height' => 794,
        'orientation' => 'landscape',
        'format' => 'A4'
    ];
    
    public $selectedElementIndex = null;

    // Variables disponibles
    public $variables = [
        '{student_name}' => 'Nombre del Estudiante',
        '{course_name}' => 'Nombre del Curso',
        '{date}' => 'Fecha de Emisión',
        '{folio}' => 'Folio Único',
        '{instructor}' => 'Nombre Instructor',
    ];

    public function mount($templateId = null)
    {
        // SOLUCIÓN: Si Livewire no inyecta el parámetro, lo intentamos tomar de la ruta
        if (!$templateId) {
            $templateId = request()->route('templateId');
        }

        if ($templateId) {
            $template = CertificateTemplate::findOrFail($templateId);
            $this->templateId = $template->id;
            $this->name = $template->name;
            
            // Cargar datos del layout
            $data = $template->layout_data ?? [];
            
            if (isset($data['elements'])) {
                $this->elements = $data['elements'];
                $this->canvasConfig = $data['canvasConfig'] ?? $this->canvasConfig;
            } else {
                // Retrocompatibilidad con datos antiguos
                $this->elements = is_array($data) ? $data : [];
            }

            // Cargar imagen de fondo (prioriza bg_image_path)
            $this->currentBg = $template->bg_image_path ?? $template->background_image;
        } else {
            // Inicializar nuevo si no hay ID
            $this->name = 'Nuevo Diploma ' . date('d-m-Y');
            $this->elements = [
                [
                    'id' => uniqid(),
                    'type' => 'text',
                    'content' => 'DIPLOMA DE HONOR',
                    'x' => 260, 'y' => 100,
                    'width' => 600, 'height' => 60,
                    'fontFamily' => 'Cinzel Decorative',
                    'fontSize' => 48,
                    'fontWeight' => '700',
                    'color' => '#1a202c',
                    'textAlign' => 'center',
                    'zIndex' => 10,
                    'rotation' => 0,
                    'locked' => false,
                    'hidden' => false
                ]
            ];
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'elements' => 'array',
            'bgImage' => 'nullable|image|max:10240',
        ]);

        $layoutData = [
            'elements' => $this->elements,
            'canvasConfig' => $this->canvasConfig
        ];

        // Preparar datos básicos
        $data = [
            'name' => $this->name,
            'layout_data' => $layoutData, 
            'is_active' => true,
        ];

        // Manejo de imagen
        if ($this->bgImage) {
            if ($this->templateId && $this->currentBg) {
                Storage::disk('public')->delete($this->currentBg);
            }
            $path = $this->bgImage->store('certificates/backgrounds', 'public');
            
            // Guardamos en ambas columnas por si acaso, o priorizamos la correcta según tu tabla
            // Asumiendo que tu migración usa 'bg_image_path'
            $data['bg_image_path'] = $path; 
            // $data['background_image'] = $path; // Descomenta si tu BD usa este nombre antiguo
            
            $this->currentBg = $path;
        }

        if ($this->templateId) {
            $template = CertificateTemplate::findOrFail($this->templateId);
            $template->update($data);
            $message = 'Diseño actualizado exitosamente.';
        } else {
            $template = CertificateTemplate::create($data);
            $this->templateId = $template->id;
            $message = 'Diseño creado exitosamente.';
        }

        session()->flash('message', $message);
        
        // Redirigir usando el nombre de ruta correcto (plural)
        return redirect()->route('admin.certificates.templates');
    }

    public function render()
    {
        return view('livewire.admin.certificate-editor')->layout('layouts.dashboard');
    }
}