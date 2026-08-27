<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use Livewire\Component;

class AcademicYearManager extends Component
{
    // ── Propiedades del formulario ───────────────────────────
    public $name = '';
    public $start_date = '';
    public $end_date = '';
    public $status = 'planning';

    // ── Estado del componente ────────────────────────────────
    public $showModal = false;
    public $editingId = null;
    public $confirmingDeletion = null;

    // ── Período modal ────────────────────────────────────────
    public $showPeriodModal = false;
    public $periodYearId = null;
    public $periods = [];

    protected function rules()
    {
        return [
            'name'       => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'status'     => 'required|in:planning,active,closed',
        ];
    }

    // ── Acciones CRUD ───────────────────────────────────────

    public function create()
    {
        $this->reset(['name', 'start_date', 'end_date', 'status', 'editingId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $year = AcademicYear::findOrFail($id);
        $this->editingId = $year->id;
        $this->name = $year->name;
        $this->start_date = $year->start_date->format('Y-m-d');
        $this->end_date = $year->end_date->format('Y-m-d');
        $this->status = $year->status;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        AcademicYear::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name'       => $this->name,
                'start_date' => $this->start_date,
                'end_date'   => $this->end_date,
                'status'     => $this->status,
            ]
        );

        // Si se activa un año, desactivar los demás
        if ($this->status === 'active' && $this->editingId) {
            AcademicYear::where('id', '!=', $this->editingId)
                ->where('status', 'active')
                ->update(['status' => 'planning']);
        }

        $this->showModal = false;
        $this->reset(['name', 'start_date', 'end_date', 'status', 'editingId']);
        session()->flash('message', $this->editingId ? 'Año escolar actualizado.' : 'Año escolar creado exitosamente.');
    }

    public function delete($id)
    {
        AcademicYear::findOrFail($id)->delete();
        $this->confirmingDeletion = null;
        session()->flash('message', 'Año escolar eliminado.');
    }

    // ── Gestión de Períodos ──────────────────────────────────

    public function managePeriods($yearId)
    {
        $this->periodYearId = $yearId;
        $year = AcademicYear::with('periods')->findOrFail($yearId);
        
        if ($year->periods->isEmpty()) {
            // Pre-llenar 4 períodos vacíos
            $this->periods = [];
            for ($i = 1; $i <= 4; $i++) {
                $this->periods[] = [
                    'id'         => null,
                    'name'       => "Período $i",
                    'number'     => $i,
                    'start_date' => '',
                    'end_date'   => '',
                    'status'     => 'upcoming',
                ];
            }
        } else {
            $this->periods = $year->periods->map(fn($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'number'     => $p->number,
                'start_date' => $p->start_date->format('Y-m-d'),
                'end_date'   => $p->end_date->format('Y-m-d'),
                'status'     => $p->status,
            ])->toArray();
        }

        $this->showPeriodModal = true;
    }

    public function savePeriods()
    {
        $this->validate([
            'periods.*.name'       => 'required|string',
            'periods.*.start_date' => 'required|date',
            'periods.*.end_date'   => 'required|date|after:periods.*.start_date',
        ]);

        foreach ($this->periods as $periodData) {
            EvaluationPeriod::updateOrCreate(
                [
                    'academic_year_id' => $this->periodYearId,
                    'number'           => $periodData['number'],
                ],
                [
                    'name'       => $periodData['name'],
                    'start_date' => $periodData['start_date'],
                    'end_date'   => $periodData['end_date'],
                    'status'     => $periodData['status'],
                ]
            );
        }

        $this->showPeriodModal = false;
        session()->flash('message', 'Períodos de evaluación guardados correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.school.academic-year-manager', [
            'years' => AcademicYear::with('periods')->orderByDesc('start_date')->get(),
        ])->layout('layouts.dashboard');
    }
}
