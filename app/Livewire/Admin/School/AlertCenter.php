<?php

namespace App\Livewire\Admin\School;

use App\Models\SchoolAlert;
use App\Services\AlertScannerService;
use Livewire\Component;
use Livewire\WithPagination;

class AlertCenter extends Component
{
    use WithPagination;

    public $filterType = '';
    public $filterSeverity = '';
    public $showResolved = false;

    // Resolution modal
    public $showResolveModal = false;
    public $resolveAlertId = null;
    public $resolveNote = '';

    public function scan()
    {
        $scanner = new AlertScannerService();
        $result = $scanner->scanAll();
        session()->flash('message', "Escaneo completado: {$result['scanned']} estudiantes revisados, {$result['alerts']} alertas nuevas.");
    }

    public function markRead($id)
    {
        SchoolAlert::findOrFail($id)->update(['is_read' => true]);
    }

    public function openResolve($id)
    {
        $this->resolveAlertId = $id;
        $this->resolveNote = '';
        $this->showResolveModal = true;
    }

    public function resolve()
    {
        if (!$this->resolveAlertId) return;

        $alert = SchoolAlert::findOrFail($this->resolveAlertId);
        $alert->resolve(auth()->id(), $this->resolveNote);

        $this->showResolveModal = false;
        session()->flash('message', 'Alerta resuelta.');
    }

    public function markAllRead()
    {
        SchoolAlert::where('is_read', false)->update(['is_read' => true]);
    }

    public function render()
    {
        $alerts = SchoolAlert::query()
            ->when(!$this->showResolved, fn($q) => $q->where('is_resolved', false))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterSeverity, fn($q) => $q->where('severity', $this->filterSeverity))
            ->with('student')
            ->orderByDesc('severity')
            ->orderByDesc('created_at')
            ->paginate(25);

        $stats = [
            'total'    => SchoolAlert::where('is_resolved', false)->count(),
            'critical' => SchoolAlert::where('is_resolved', false)->where('severity', 'critical')->count(),
            'warning'  => SchoolAlert::where('is_resolved', false)->where('severity', 'warning')->count(),
            'unread'   => SchoolAlert::where('is_read', false)->count(),
        ];

        return view('livewire.admin.school.alert-center', [
            'alerts' => $alerts,
            'stats'  => $stats,
            'types'  => SchoolAlert::TYPES,
        ])->layout('layouts.dashboard');
    }
}
