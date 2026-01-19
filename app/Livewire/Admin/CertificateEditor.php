<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\Storage;

class CertificateEditor extends Component
{
    use WithFileUploads;

    public $templateId; // Propiedad pública para el ID
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
        // 1. Estrategia Robusta de Captura de ID
        // Prioridad: Argumento > Propiedad Pública (Livewire) > Parámetro de Ruta > Query String
        $id = $templateId 
              ?? $this->templateId 
              ?? request()->route('templateId') 
              ?? request()->query('templateId');

        $template = null;

        if ($id) {
            $template = CertificateTemplate::find($id);
        }

        // 2. Cargar Estado según si existe la plantilla o es nueva
        if ($template) {
            $this->loadTemplate($template);
        } else {
            $this->initializeNewTemplate();
        }
    }

    protected function loadTemplate($template)
    {
        $this->templateId = $template->id;
        $this->name = $template->name;
        
        // Decodificar datos del layout
        $data = $template->layout_data;
        
        // Normalizar estructura (Soporte para formato nuevo y antiguo)
        if (is_array($data) && isset($data['elements'])) {
            // Formato Nuevo: { elements: [...], canvasConfig: {...} }
            $this->elements = $data['elements'];
            if (isset($data['canvasConfig']) && is_array($data['canvasConfig'])) {
                $this->canvasConfig = array_merge($this->canvasConfig, $data['canvasConfig']);
            }
        } else {
            // Formato Antiguo: [ {...}, {...} ] (Solo elementos)
            $this->elements = is_array($data) ? $data : [];
        }

        // Cargar imagen (prioriza columna nueva, fallback a antigua)
        $this->currentBg = $template->bg_image_path ?? $template->background_image;
    }

    protected function initializeNewTemplate()
    {
        $this->templateId = null;
        $this->name = 'Nuevo Diploma ' . date('d/m/Y');
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

        $data = [
            'name' => $this->name,
            'layout_data' => $layoutData, 
            'is_active' => true,
        ];

        // Manejo de imagen
        if ($this->bgImage) {
            // Eliminar anterior solo si existe y es diferente
            if ($this->templateId && $this->currentBg) {
                if (Storage::disk('public')->exists($this->currentBg)) {
                    Storage::disk('public')->delete($this->currentBg);
                }
            }
            
            $path = $this->bgImage->store('certificates/backgrounds', 'public');
            $data['bg_image_path'] = $path; 
            $this->currentBg = $path;
        }

        if ($this->templateId) {
            $template = CertificateTemplate::find($this->templateId);
            
            if ($template) {
                $template->update($data);
                $message = 'Diseño actualizado exitosamente.';
            } else {
                // Si tenemos un ID en memoria pero no en BD (raro), creamos uno nuevo
                $template = CertificateTemplate::create($data);
                $this->templateId = $template->id;
                $message = 'Diseño recreado exitosamente.';
            }
        } else {
            $template = CertificateTemplate::create($data);
            $this->templateId = $template->id;
            $message = 'Diseño creado exitosamente.';
        }

        session()->flash('message', $message);
        
        return redirect()->route('admin.certificates.templates');
    }

    public function render()
    {
        return view('livewire.admin.certificate-editor')->layout('layouts.dashboard');
    }
}