<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\PromotionRecord;
use App\Models\Section;
use App\Models\Student;
use Livewire\Component;

class ReinscriptionManager extends Component
{
    public $fromYearId = '';
    public $toYearId = '';
    public $preview = [];
    public $isProcessing = false;

    public function generatePreview()
    {
        if (!$this->fromYearId || !$this->toYearId) return;

        $promotions = PromotionRecord::where('academic_year_id', $this->fromYearId)
            ->with(['student', 'gradeLevel', 'section'])
            ->get();

        $gradeLevels = GradeLevel::orderBy('order')->get();

        $this->preview = [];

        foreach ($promotions as $promo) {
            $student = $promo->student;
            if (!$student || $student->status !== 'Activo') continue;

            $currentGrade = $promo->gradeLevel;
            $nextGrade = null;
            $action = 'skip';

            if ($promo->result === 'promoted') {
                // Find next grade level
                $nextGrade = $gradeLevels->firstWhere('order', ($currentGrade?->order ?? 0) + 1);
                $action = $nextGrade ? 'promote' : 'graduated';
            } elseif ($promo->result === 'retained') {
                $nextGrade = $currentGrade;
                $action = 'retain';
            } elseif ($promo->result === 'withdrawn' || $promo->result === 'transferred') {
                $action = 'exclude';
            } elseif ($promo->result === 'graduated') {
                $action = 'graduated';
            }

            $this->preview[] = [
                'student_id'   => $student->id,
                'student_name' => $student->full_name,
                'current_grade' => $currentGrade?->short_name ?? '—',
                'current_section' => $promo->section?->name ?? '',
                'result'       => PromotionRecord::RESULTS[$promo->result] ?? $promo->result,
                'next_grade'   => $nextGrade?->short_name ?? ($action === 'graduated' ? 'Graduado' : '—'),
                'next_grade_id' => $nextGrade?->id,
                'action'       => $action,
                'avg'          => $promo->final_average,
            ];
        }
    }

    public function processReinscription()
    {
        if (empty($this->preview) || !$this->toYearId) return;

        $this->isProcessing = true;
        $processed = 0;

        foreach ($this->preview as $item) {
            if ($item['action'] === 'exclude' || $item['action'] === 'graduated' || $item['action'] === 'skip') continue;

            $student = Student::find($item['student_id']);
            if (!$student || !$item['next_grade_id']) continue;

            $student->update([
                'grade_level_id' => $item['next_grade_id'],
                'section_id'     => null, // Will need manual section assignment
            ]);

            $processed++;
        }

        $this->isProcessing = false;
        session()->flash('message', "Reinscripción completada: {$processed} estudiantes actualizados. Asigne secciones manualmente.");
        $this->preview = [];
    }

    public function render()
    {
        $years = AcademicYear::orderByDesc('start_date')->get();

        return view('livewire.admin.school.reinscription-manager', [
            'years' => $years,
        ])->layout('layouts.dashboard');
    }
}
