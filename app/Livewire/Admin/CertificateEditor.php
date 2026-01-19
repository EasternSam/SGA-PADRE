<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class CertificateEditor extends Component
{
    use WithFileUploads;

    public $templateId;
    public $name = 'Nuevo Diseño de Diploma';
    public $bgImage;
    public $currentBg;
    
    // Array con los elementos del lienzo
    public $elements = []; 
    
    public $selectedElementIndex = null;

    // Variables disponibles
    public $variables = [
        '{student_name}' => 'Nombre del Estudiante',
        '{course_name}' => 'Nombre del Curso',
        '{date}' => 'Fecha de Emisión',
        '{folio}' => 'Folio Único',
        '{director_name}' => 'Nombre Director',
        '{institution_name}' => 'Nombre Institución',
    ];

    public function mount($templateId = null)
    {
        if ($templateId) {
            $template = CertificateTemplate::findOrFail($templateId);
            $this->templateId = $template->id;
            $this->name = $template->name;
            $this->elements = $template->layout_data ?? [];
            $this->currentBg = $template->background_image;
        } else {
            // Diseño por defecto al iniciar
            $this->elements = [
                [
                    'type' => 'text',
                    'content' => 'DIPLOMA DE HONOR',
                    'x' => 100, 'y' => 50,
                    'width' => 600, 'height' => 60,
                    'fontFamily' => 'Cinzel Decorative',
                    'fontSize' => 48,
                    'fontWeight' => 'bold',
                    'color' => '#1a202c',
                    'textAlign' => 'center'
                ],
                [
                    'type' => 'variable',
                    'content' => '{student_name}',
                    'x' => 100, 'y' => 200,
                    'width' => 600, 'height' => 80,
                    'fontFamily' => 'Pinyon Script',
                    'fontSize' => 56,
                    'fontWeight' => 'normal',
                    'color' => '#b49b5a',
                    'textAlign' => 'center'
                ]
            ];
        }
    }

    public function addElement($type = 'text')
    {
        $this->elements[] = [
            'type' => $type,
            'content' => $type === 'text' ? 'Texto Nuevo' : '{student_name}',
            'x' => 50, 
            'y' => 50,
            'width' => 300, 
            'height' => 50,
            'fontFamily' => 'EB Garamond',
            'fontSize' => 20,
            'fontWeight' => 'normal',
            'color' => '#000000',
            'textAlign' => 'left',
            'zIndex' => 10
        ];
        
        $this->selectedElementIndex = count($this->elements) - 1;
    }

    public function removeElement($index)
    {
        unset($this->elements[$index]);
        $this->elements = array_values($this->elements);
        $this->selectedElementIndex = null;
    }

    public function selectElement($index)
    {
        $this->selectedElementIndex = $index;
    }

    public function updateElementPosition($index, $x, $y)
    {
        if (isset($this->elements[$index])) {
            $this->elements[$index]['x'] = $x;
            $this->elements[$index]['y'] = $y;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = [
            'name' => $this->name,
            'layout_data' => $this->elements,
            'is_active' => true,
        ];

        if ($this->bgImage) {
            $path = $this->bgImage->store('certificates/backgrounds', 'public');
            $data['background_image'] = $path;
            $this->currentBg = $path;
        }

        if ($this->templateId) {
            $template = CertificateTemplate::find($this->templateId);
            $template->update($data);
        } else {
            $template = CertificateTemplate::create($data);
            $this->templateId = $template->id;
        }

        session()->flash('message', 'Diseño guardado exitosamente.');
    }

    public function render()
    {
        return view('livewire.admin.certificate-editor')->layout('layouts.dashboard');
    }
}