<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\Storage;

class CertificateTemplatesIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function delete($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        
        // Eliminar imagen asociada si existe
        if ($template->background_image) {
            Storage::disk('public')->delete($template->background_image);
        }

        $template->delete();
        session()->flash('message', 'Plantilla eliminada correctamente.');
    }

    public function render()
    {
        $templates = CertificateTemplate::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('livewire.admin.certificate-templates-index', [
            'templates' => $templates
        ])->layout('layouts.dashboard');
    }
}