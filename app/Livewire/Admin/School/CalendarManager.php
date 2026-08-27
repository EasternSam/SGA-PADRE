<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\SchoolCalendar;
use Livewire\Component;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CalendarManager extends Component
{
    public $viewMonth;
    public $viewYear;
    public $calendarDays = [];

    // Modal
    public $showModal = false;
    public $editDate = '';
    public $editType = 'holiday';
    public $editName = '';
    public $editDescription = '';
    public $editId = null;

    // Stats
    public $stats = ['school_days' => 0, 'holidays' => 0, 'vacations' => 0, 'events' => 0];

    public function mount()
    {
        $this->viewMonth = now()->month;
        $this->viewYear = now()->year;
        $this->loadCalendar();
    }

    public function prevMonth()
    {
        $date = Carbon::create($this->viewYear, $this->viewMonth)->subMonth();
        $this->viewMonth = $date->month;
        $this->viewYear = $date->year;
        $this->loadCalendar();
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->viewYear, $this->viewMonth)->addMonth();
        $this->viewMonth = $date->month;
        $this->viewYear = $date->year;
        $this->loadCalendar();
    }

    public function loadCalendar()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $start = Carbon::create($this->viewYear, $this->viewMonth, 1);
        $end = $start->copy()->endOfMonth();

        // Get entries for this month
        $entries = SchoolCalendar::where('academic_year_id', $activeYear->id)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn($e) => $e->date->format('Y-m-d'));

        // Build calendar grid
        $this->calendarDays = [];
        $current = $start->copy()->startOfWeek(Carbon::MONDAY);
        $lastDay = $end->copy()->endOfWeek(Carbon::SUNDAY);

        while ($current <= $lastDay) {
            $key = $current->format('Y-m-d');
            $entry = $entries[$key] ?? null;
            $isWeekend = $current->isWeekend();
            $isCurrentMonth = $current->month === $this->viewMonth;

            $this->calendarDays[] = [
                'date'           => $key,
                'day'            => $current->day,
                'is_current_month' => $isCurrentMonth,
                'is_today'       => $current->isToday(),
                'is_weekend'     => $isWeekend,
                'entry_id'       => $entry?->id,
                'type'           => $entry?->type ?? ($isWeekend ? 'weekend' : null),
                'name'           => $entry?->name ?? '',
                'color'          => $entry ? (SchoolCalendar::TYPE_COLORS[$entry->type] ?? '#9ca3af') : null,
            ];

            $current->addDay();
        }

        // Stats for the entire year
        $this->stats = [
            'school_days' => SchoolCalendar::where('academic_year_id', $activeYear->id)->where('type', 'school_day')->count(),
            'holidays'    => SchoolCalendar::where('academic_year_id', $activeYear->id)->where('type', 'holiday')->count(),
            'vacations'   => SchoolCalendar::where('academic_year_id', $activeYear->id)->where('type', 'vacation')->count(),
            'events'      => SchoolCalendar::where('academic_year_id', $activeYear->id)->where('type', 'event')->count(),
        ];
    }

    public function openDay($date)
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $entry = SchoolCalendar::where('academic_year_id', $activeYear->id)
            ->whereDate('date', $date)->first();

        $this->editDate = $date;
        $this->editId = $entry?->id;
        $this->editType = $entry?->type ?? 'holiday';
        $this->editName = $entry?->name ?? '';
        $this->editDescription = $entry?->description ?? '';
        $this->showModal = true;
    }

    public function saveEntry()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $this->validate([
            'editDate' => 'required|date',
            'editType' => 'required',
        ]);

        SchoolCalendar::updateOrCreate(
            ['id' => $this->editId],
            [
                'academic_year_id' => $activeYear->id,
                'date'             => $this->editDate,
                'type'             => $this->editType,
                'name'             => $this->editName ?: null,
                'description'      => $this->editDescription ?: null,
                'affects_attendance' => !in_array($this->editType, ['holiday', 'vacation']),
            ]
        );

        $this->showModal = false;
        $this->loadCalendar();
        session()->flash('message', 'Día actualizado.');
    }

    public function deleteEntry()
    {
        if ($this->editId) {
            SchoolCalendar::find($this->editId)?->delete();
        }
        $this->showModal = false;
        $this->loadCalendar();
    }

    public function generateSchoolDays()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear || !$activeYear->start_date || !$activeYear->end_date) {
            session()->flash('error', 'El año escolar debe tener fechas de inicio y fin.');
            return;
        }

        $period = CarbonPeriod::create($activeYear->start_date, $activeYear->end_date);
        $count = 0;

        foreach ($period as $date) {
            if ($date->isWeekend()) continue;

            SchoolCalendar::firstOrCreate(
                ['academic_year_id' => $activeYear->id, 'date' => $date->format('Y-m-d')],
                ['type' => 'school_day', 'affects_attendance' => true]
            );
            $count++;
        }

        $this->loadCalendar();
        session()->flash('message', "Se generaron $count días lectivos.");
    }

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        $monthName = Carbon::create($this->viewYear, $this->viewMonth)->translatedFormat('F Y');

        return view('livewire.admin.school.calendar-manager', [
            'activeYear' => $activeYear,
            'monthName'  => $monthName,
            'types'      => SchoolCalendar::TYPES,
        ])->layout('layouts.dashboard');
    }
}
