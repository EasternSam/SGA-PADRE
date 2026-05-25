<?php

namespace App\Livewire\Admin\School;

use App\Models\CommunicationLog;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class CommunicationManager extends Component
{
    use WithPagination;

    public $filterChannel = '';

    // Send modal
    public $showSendModal = false;
    public $channel = 'internal';
    public $sendType = 'all';
    public $subject = '';
    public $body = '';
    public $section_id = '';

    public function openSend()
    {
        $this->reset(['channel', 'sendType', 'subject', 'body', 'section_id']);
        $this->channel = 'internal';
        $this->sendType = 'all';
        $this->showSendModal = true;
    }

    public function send()
    {
        $this->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        // Count recipients
        $count = 0;
        if ($this->sendType === 'all') {
            $count = Guardian::count();
        } elseif ($this->sendType === 'section' && $this->section_id) {
            $studentIds = Student::where('section_id', $this->section_id)->where('status', 'Activo')->pluck('id');
            $count = Guardian::whereHas('students', fn($q) => $q->whereIn('student_id', $studentIds))->count();
        }

        CommunicationLog::create([
            'channel'          => $this->channel,
            'type'             => $this->sendType,
            'subject'          => $this->subject,
            'body'             => $this->body,
            'sent_by'          => auth()->id(),
            'section_id'       => $this->section_id ?: null,
            'recipients_count' => $count,
            'status'           => 'sent',
            'sent_at'          => now(),
        ]);

        $this->showSendModal = false;
        session()->flash('message', "Comunicado enviado a {$count} destinatarios.");
    }

    public function delete($id)
    {
        CommunicationLog::findOrFail($id)->delete();
    }

    public function render()
    {
        $logs = CommunicationLog::query()
            ->when($this->filterChannel, fn($q) => $q->where('channel', $this->filterChannel))
            ->with('sender')
            ->orderByDesc('created_at')
            ->paginate(20);

        $sections = Section::whereHas('academicYear', fn($q) => $q->where('status', 'active'))
            ->with('gradeLevel')
            ->orderBy('grade_level_id')
            ->get();

        // Stats
        $totalSent = CommunicationLog::where('status', 'sent')->count();
        $thisMonth = CommunicationLog::where('status', 'sent')->whereMonth('sent_at', now()->month)->count();

        return view('livewire.admin.school.communication-manager', [
            'logs'       => $logs,
            'channels'   => CommunicationLog::CHANNELS,
            'types'      => CommunicationLog::TYPES,
            'sections'   => $sections,
            'totalSent'  => $totalSent,
            'thisMonth'  => $thisMonth,
        ])->layout('layouts.dashboard');
    }
}
