<?php

namespace App\Livewire\Admin\School;

use App\Models\SchoolConfig;
use Livewire\Component;
use Livewire\WithFileUploads;

class SchoolSettings extends Component
{
    use WithFileUploads;

    public $school_name = '';
    public $minerd_code = '';
    public $rnc = '';
    public $regional = '';
    public $district = '';
    public $shift = 'matutina';
    public $school_type = 'privado';
    public $level = 'primario_secundario';
    public $director_name = '';
    public $director_cedula = '';
    public $address = '';
    public $city = '';
    public $province = '';
    public $phone = '';
    public $email = '';
    public $website = '';
    public $motto = '';
    public $new_logo;

    public function mount()
    {
        $config = SchoolConfig::current();
        if ($config) {
            $this->fill($config->only([
                'school_name', 'minerd_code', 'rnc', 'regional', 'district',
                'shift', 'school_type', 'level', 'director_name', 'director_cedula',
                'address', 'city', 'province', 'phone', 'email', 'website', 'motto',
            ]));
        }
    }

    public function save()
    {
        $this->validate([
            'school_name' => 'required|string|max:200',
            'minerd_code' => 'nullable|string|max:20',
            'rnc' => 'nullable|string|max:15',
            'regional' => 'nullable|string',
            'shift' => 'required',
            'school_type' => 'required',
            'level' => 'required',
            'new_logo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'school_name' => $this->school_name,
            'minerd_code' => $this->minerd_code,
            'rnc' => $this->rnc,
            'regional' => $this->regional,
            'district' => $this->district,
            'shift' => $this->shift,
            'school_type' => $this->school_type,
            'level' => $this->level,
            'director_name' => $this->director_name,
            'director_cedula' => $this->director_cedula,
            'address' => $this->address,
            'city' => $this->city,
            'province' => $this->province,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'motto' => $this->motto,
        ];

        if ($this->new_logo) {
            $data['logo_path'] = $this->new_logo->store('school', 'public');
        }

        SchoolConfig::updateOrCreate(['id' => SchoolConfig::current()?->id ?? 0], $data);

        session()->flash('message', 'Configuración del centro educativo guardada.');
    }

    public function render()
    {
        return view('livewire.admin.school.school-settings', [
            'regionals' => SchoolConfig::REGIONALS,
            'shifts' => SchoolConfig::SHIFTS,
            'schoolTypes' => SchoolConfig::SCHOOL_TYPES,
            'levels' => SchoolConfig::LEVELS,
            'provinces' => SchoolConfig::PROVINCES,
            'currentConfig' => SchoolConfig::current(),
        ])->layout('layouts.dashboard');
    }
}
