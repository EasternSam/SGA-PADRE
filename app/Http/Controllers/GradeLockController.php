<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\GradeLockPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GradeLockController extends Controller
{
    /**
     * Display grade lock management screen.
     */
    public function index(): Response
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $periods = [];
        if ($activeYear) {
            $periodModels = EvaluationPeriod::where('academic_year_id', $activeYear->id)
                ->orderBy('number')
                ->get();

            $locks = GradeLockPeriod::whereIn('evaluation_period_id', $periodModels->pluck('id'))
                ->with('lockedByUser')
                ->get()
                ->keyBy('evaluation_period_id');

            $periods = $periodModels->map(function ($p) use ($locks) {
                $lock = $locks->get($p->id);
                $isLockedEffective = $lock ? ($lock->is_locked || ($lock->lock_date && $lock->lock_date->isPast())) : false;

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'number' => $p->number,
                    'status' => $p->status,
                    'start_date' => $p->start_date?->format('d/m/Y'),
                    'end_date' => $p->end_date?->format('d/m/Y'),
                    'lock_id' => $lock?->id,
                    'is_locked' => (bool)($lock?->is_locked ?? false),
                    'is_effective_locked' => $isLockedEffective,
                    'lock_date' => $lock?->lock_date?->format('Y-m-d') ?? '',
                    'lock_reason' => $lock?->lock_reason ?? '',
                    'locked_by' => $lock?->lockedByUser?->name ?? null,
                    'updated_at' => $lock?->updated_at?->format('d/m/Y H:i') ?? null,
                ];
            })->values()->toArray();
        }

        return Inertia::render('Grades/LockManager', [
            'activeYear' => $activeYear ? [
                'id' => $activeYear->id,
                'name' => $activeYear->name,
            ] : null,
            'periods' => $periods,
        ]);
    }

    /**
     * Toggle lock status directly.
     */
    public function toggleLock(Request $request, $periodId)
    {
        $period = EvaluationPeriod::findOrFail($periodId);
        $lock = GradeLockPeriod::firstOrNew(['evaluation_period_id' => $period->id]);

        $newLockedState = !$lock->is_locked;
        $lock->is_locked = $newLockedState;
        $lock->locked_by = auth()->id();
        $lock->lock_reason = $newLockedState ? 'Bloqueo manual por administrador' : 'Desbloqueo manual por administrador';
        $lock->save();

        $action = $newLockedState ? 'bloqueado' : 'desbloqueado';
        return back()->with('success', "El período {$period->name} ha sido {$action} exitosamente.");
    }

    /**
     * Save date or lock configuration for a period.
     */
    public function saveLock(Request $request, $periodId)
    {
        $request->validate([
            'lock_date' => 'nullable|date',
            'is_locked' => 'nullable|boolean',
            'lock_reason' => 'nullable|string|max:255',
        ]);

        $period = EvaluationPeriod::findOrFail($periodId);

        GradeLockPeriod::updateOrCreate(
            ['evaluation_period_id' => $period->id],
            [
                'lock_date' => $request->input('lock_date') ?: null,
                'is_locked' => (bool)$request->input('is_locked', false),
                'lock_reason' => $request->input('lock_reason'),
                'locked_by' => auth()->id(),
            ]
        );

        return back()->with('success', "Configuración de bloqueo para {$period->name} actualizada.");
    }
}
