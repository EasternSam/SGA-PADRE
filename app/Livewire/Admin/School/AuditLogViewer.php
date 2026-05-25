<?php

namespace App\Livewire\Admin\School;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public $filterAction = '';
    public $filterUser = '';
    public $filterModel = '';
    public $search = '';

    public function render()
    {
        $logs = AuditLog::query()
            ->when($this->filterAction, fn($q) => $q->where('action', $this->filterAction))
            ->when($this->filterUser, fn($q) => $q->where('user_id', $this->filterUser))
            ->when($this->filterModel, fn($q) => $q->where('model_type', 'like', '%' . $this->filterModel . '%'))
            ->when($this->search, fn($q) => $q->where('description', 'like', '%' . $this->search . '%'))
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(30);

        $actions = AuditLog::ACTIONS;
        $users = \App\Models\User::orderBy('name')->pluck('name', 'id');

        return view('livewire.admin.school.audit-log-viewer', [
            'logs'    => $logs,
            'actions' => $actions,
            'users'   => $users,
        ])->layout('layouts.dashboard');
    }
}
