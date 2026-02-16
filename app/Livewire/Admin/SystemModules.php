<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Helpers\SaaS;
use App\Services\AddonInstallerService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class SystemModules extends Component
{
    public $availableModules = [];

    // Catálogo visual local (Solo para mostrar info bonita antes de instalar)
    public $catalog = [
        'hr' => ['title' => 'Recursos Humanos', 'icon' => '👔', 'desc' => 'Nómina, asistencia docente y gestión de personal.'],
        'library' => ['title' => 'Biblioteca Digital', 'icon' => '📚', 'desc' => 'Préstamos de libros, catálogo y devoluciones.'],
        'inventory' => ['title' => 'Control de Inventario', 'icon' => '📦', 'desc' => 'Activos fijos, stock y suministros.'],
    ];

    public function mount()
    {
        $this->refreshStatus();
    }

    public function refreshStatus()
    {
        // Obtener features permitidos por la licencia
        $allowedFeatures = Cache::get('saas_active_features', []);
        
        $installer = new AddonInstallerService();
        $this->availableModules = [];

        foreach ($this->catalog as $code => $info) {
            // Solo mostramos si la licencia lo permite
            if (in_array($code, $allowedFeatures)) {
                $this->availableModules[] = [
                    'code' => $code,
                    'title' => $info['title'],
                    'icon' => $info['icon'],
                    'description' => $info['desc'],
                    'is_installed' => $installer->isInstalled($code),
                ];
            }
        }
    }

    public function installModule($code)
    {
        $installer = new AddonInstallerService();
        $result = $installer->install($code);

        if ($result['success']) {
            session()->flash('message', $result['message']);
            return redirect()->route('admin.modules.index'); // Recargar para ver cambios
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.system-modules');
    }
}