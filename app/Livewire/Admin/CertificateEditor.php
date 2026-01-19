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
    public $bgImage; // Para la subida temporal
    public $currentBg; // Ruta guardada en BD
    
    // Array con los elementos del lienzo
    public $elements = []; 
    
    // Configuración del lienzo (Orientación, tamaño)
    public $canvasConfig = [
        'width' => 1123, 
        'height' => 794,
        'orientation' => 'landscape',
        'format' => 'A4'
    ];
    
    public $selectedElementIndex = null;

    // Variables disponibles para el usuario
    public $variables = [
        '{student_name}' => 'Nombre del Estudiante',
        '{course_name}' => 'Nombre del Curso',
        '{date}' => 'Fecha de Emisión',
        '{folio}' => 'Folio Único',
        '{director_name}' => 'Nombre Director',
        '{institution_name}' => 'Nombre Institución',
        '{instructor}' => 'Nombre Instructor',
    ];

    public function mount($templateId = null)
    {
        if ($templateId) {
            $template = CertificateTemplate::findOrFail($templateId);
            $this->templateId = $template->id;
            $this->name = $template->name;
            
            // Recuperamos datos. Si layout_data es array, lo usamos.
            // La estructura guardada será {elements: [...], canvasConfig: {...}}
            $data = $template->layout_data ?? [];
            
            if (isset($data['elements'])) {
                // Formato nuevo con config
                $this->elements = $data['elements'];
                $this->canvasConfig = $data['canvasConfig'] ?? $this->canvasConfig;
            } else {
                // Migración de formato antiguo (si existía)
                $this->elements = is_array($data) ? $data : [];
            }

            $this->currentBg = $template->background_image;
        } else {
            // Diseño por defecto al iniciar nuevo
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
            'bgImage' => 'nullable|image|max:10240', // 10MB Max
        ]);

        // 1. Estructura de datos a guardar (JSON)
        $layoutData = [
            'elements' => $this->elements,
            'canvasConfig' => $this->canvasConfig
        ];

        // 2. Preparar datos del modelo
        $data = [
            'name' => $this->name,
            'layout_data' => $layoutData, 
            'is_active' => true,
        ];

        // 3. Manejo de subida de imagen
        if ($this->bgImage) {
            // Si ya existe una imagen previa y estamos editando, la borramos para no acumular basura
            if ($this->templateId && $this->currentBg) {
                Storage::disk('public')->delete($this->currentBg);
            }

            // Guardar nueva imagen
            $path = $this->bgImage->store('certificates/backgrounds', 'public');
            $data['background_image'] = $path;
            $this->currentBg = $path;
        } elseif ($this->currentBg === null && $this->templateId) {
            // Caso en que el usuario borró el fondo explícitamente (si implementas esa función)
            $data['background_image'] = null;
        }

        // 4. Crear o Actualizar en BD
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
        
        // Redirigimos al listado de plantillas
        return redirect()->route('admin.certificates.templates');
    }

    // Métodos auxiliares requeridos por la vista para evitar errores de Livewire
    // aunque la lógica pesada está en Alpine, Livewire necesita estos stubs si se llaman via $wire
    public function render()
    {
        return view('livewire.admin.certificate-editor')->layout('layouts.dashboard');
    }
}