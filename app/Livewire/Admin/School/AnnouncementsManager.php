<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\SchoolAnnouncement;
use App\Models\Section;
use Livewire\Component;
use Livewire\WithPagination;

class AnnouncementsManager extends Component
{
    use WithPagination;

    public $filterType = '';
    public $filterPriority = '';
    public $search = '';

    // Modal
    public $showModal = false;
    public $editingId = null;
    public $title = '';
    public $body = '';
    public $type = 'announcement';
    public $priority = 'normal';
    public $audience = 'all';
    public $grade_level_id = '';
    public $section_id = '';
    public $publish_date = '';
    public $expiry_date = '';
    public $requires_acknowledgment = false;

    // Preview
    public $showPreview = false;
    public $previewAnnouncement = null;

    public function create()
    {
        $this->reset(['title', 'body', 'type', 'priority', 'audience', 'grade_level_id', 'section_id', 'expiry_date', 'requires_acknowledgment', 'editingId']);
        $this->type = 'announcement';
        $this->priority = 'normal';
        $this->audience = 'all';
        $this->publish_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit($id)
    {
        $a = SchoolAnnouncement::findOrFail($id);
        $this->editingId = $a->id;
        $this->title = $a->title;
        $this->body = $a->body;
        $this->type = $a->type;
        $this->priority = $a->priority;
        $this->audience = $a->audience;
        $this->grade_level_id = $a->grade_level_id ?? '';
        $this->section_id = $a->section_id ?? '';
        $this->publish_date = $a->publish_date?->format('Y-m-d');
        $this->expiry_date = $a->expiry_date?->format('Y-m-d') ?? '';
        $this->requires_acknowledgment = $a->requires_acknowledgment;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string|min:10',
            'type'         => 'required',
            'priority'     => 'required',
            'audience'     => 'required',
            'publish_date' => 'required|date',
        ]);

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) {
            session()->flash('error', 'No hay año escolar activo.');
            return;
        }

        SchoolAnnouncement::updateOrCreate(
            ['id' => $this->editingId],
            [
                'academic_year_id' => $activeYear->id,
                'author_id'        => auth()->id(),
                'title'            => $this->title,
                'body'             => $this->body,
                'type'             => $this->type,
                'priority'         => $this->priority,
                'audience'         => $this->audience,
                'grade_level_id'   => $this->grade_level_id ?: null,
                'section_id'       => $this->section_id ?: null,
                'publish_date'     => $this->publish_date,
                'expiry_date'      => $this->expiry_date ?: null,
                'is_published'     => true,
                'requires_acknowledgment' => $this->requires_acknowledgment,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->editingId ? 'Comunicación actualizada.' : 'Comunicación publicada exitosamente.');
    }

    public function preview($id)
    {
        $this->previewAnnouncement = SchoolAnnouncement::with(['author', 'gradeLevel', 'section'])->findOrFail($id);
        $this->showPreview = true;
    }

    public function togglePublish($id)
    {
        $a = SchoolAnnouncement::findOrFail($id);
        $a->update(['is_published' => !$a->is_published]);
    }

    public function delete($id)
    {
        SchoolAnnouncement::findOrFail($id)->delete();
        session()->flash('message', 'Comunicación eliminada.');
    }

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $query = SchoolAnnouncement::with(['author', 'gradeLevel', 'section'])
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('publish_date')
            ->orderByDesc('created_at');

        $gradeLevels = GradeLevel::active()->ordered()->get();
        $sections = $this->grade_level_id && $activeYear
            ? Section::where('academic_year_id', $activeYear->id)
                ->where('grade_level_id', $this->grade_level_id)
                ->orderBy('name')->get()
            : collect();

        return view('livewire.admin.school.announcements-manager', [
            'announcements' => $query->paginate(15),
            'activeYear'    => $activeYear,
            'gradeLevels'   => $gradeLevels,
            'sections'      => $sections,
            'types'         => SchoolAnnouncement::TYPES,
            'priorities'    => SchoolAnnouncement::PRIORITIES,
            'audiences'     => SchoolAnnouncement::AUDIENCES,
        ])->layout('layouts.dashboard');
    }
}
