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

    // Catálogo local visual (Solo para mostrar nombres bonitos e iconos antes de instalar)
    // Debe coincidir con los códigos que usas en el Maestro
    public $catalog = [
        'hr' => ['title' => 'Recursos Humanos', 'icon' => '👔', 'desc' => 'Nómina y asistencia docente.'],
        'inventory' => ['title' => 'Inventario', 'icon' => '📦', 'desc' => 'Control de stock y activos.'],
        'library' => ['title' => 'Biblioteca', 'icon' => '📚', 'desc' => 'Préstamos de libros.'],
        'finance' => ['title' => 'Finanzas Avanzadas', 'icon' => '💰', 'desc' => 'Reportes y facturación fiscal.'],
    ];

    public function mount()
    {
        $this->refreshStatus();
    }

    public function refreshStatus()
    {
        // 1. Obtener features permitidos por la licencia actual
        // (Esto viene de la caché que llena el Middleware CheckSaaSProfile)
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
            // Recargar para actualizar estado y menú
            return redirect()->route('admin.modules.index');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.system-modules');
    }
}