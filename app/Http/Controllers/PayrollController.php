<?php

namespace App\Http\Controllers;

use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    /**
     * Display list of payroll runs / lots.
     */
    public function index(Request $request): Response
    {
        $payrolls = Payroll::withCount('items')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->through(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'start_date' => $p->start_date?->format('d/m/Y'),
                'end_date' => $p->end_date?->format('d/m/Y'),
                'start_date_raw' => $p->start_date?->format('Y-m-d'),
                'end_date_raw' => $p->end_date?->format('Y-m-d'),
                'status' => $p->status,
                'total_amount' => (float)$p->total_amount,
                'items_count' => $p->items_count,
                'created_at' => $p->created_at?->format('d/m/Y H:i'),
            ]);

        $totalPaidYTD = Payroll::where('status', 'Pagado')
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $pendingDraftsCount = Payroll::where('status', 'Borrador')->count();
        $activeEmployeesCount = Employee::where('status', 'Activo')->count();

        return Inertia::render('Hr/Payroll/Index', [
            'payrolls' => $payrolls,
            'stats' => [
                'total_paid_ytd' => (float)$totalPaidYTD,
                'pending_drafts' => $pendingDraftsCount,
                'active_employees' => $activeEmployeesCount,
            ],
            'defaultDates' => [
                'name' => 'Nómina ' . Carbon::now()->translatedFormat('F Y'),
                'start_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'end_date' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Generate automatic payroll lot.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cycle_type' => 'required|in:Quincenal,Mensual',
        ]);

        DB::beginTransaction();
        try {
            $payroll = Payroll::create([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'Borrador',
                'total_amount' => 0,
            ]);

            $employees = Employee::with('user')->where('status', 'Activo')->get();
            $totalPayroll = 0;

            foreach ($employees as $emp) {
                $baseAmount = 0;
                $details = [];

                if ($emp->contract_type === 'Mensual') {
                    $baseAmount = $validated['cycle_type'] === 'Quincenal'
                        ? ((float)$emp->base_salary / 2)
                        : (float)$emp->base_salary;
                    $details['formula'] = $validated['cycle_type'] === 'Quincenal' ? 'Salario Base / 2' : 'Salario Base Completo';
                } else {
                    // Por Horas: conteo de entradas en el rango
                    $daysWorked = $emp->attendances()
                        ->whereBetween('punch_time', [$validated['start_date'] . ' 00:00:00', $validated['end_date'] . ' 23:59:59'])
                        ->where('punch_type', 0)
                        ->count();

                    $hoursWorked = $daysWorked * 4;
                    $baseAmount = $hoursWorked * (float)$emp->hourly_rate;

                    $details['days_worked'] = $daysWorked;
                    $details['estimated_hours'] = $hoursWorked;
                    $details['hourly_rate'] = (float)$emp->hourly_rate;
                    $details['formula'] = "{$hoursWorked} hrs * RD$ " . number_format($emp->hourly_rate, 2);
                }

                // Deducciones de Ley Dominicana (SFS 3.04%, AFP 2.87% => 5.91%)
                $deductions = round($baseAmount * 0.0591, 2);
                $netAmount = round($baseAmount - $deductions, 2);

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $emp->id,
                    'base_amount' => $baseAmount,
                    'deductions' => $deductions,
                    'net_amount' => $netAmount,
                    'details' => $details,
                ]);

                $totalPayroll += $netAmount;
            }

            $payroll->update(['total_amount' => $totalPayroll]);

            DB::commit();
            return back()->with('success', "Lote de nómina generado exitosamente con {$employees->count()} empleados procesados.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al generar nómina: ' . $e->getMessage());
        }
    }

    /**
     * Approve and pay payroll batch, posting accounting records.
     */
    public function approveAndPay(Payroll $payroll)
    {
        if ($payroll->status !== 'Borrador') {
            return back()->with('error', 'Esta nómina ya fue procesada anteriormente.');
        }

        DB::beginTransaction();
        try {
            $payroll->update(['status' => 'Pagado']);

            // Integración Financiera y Contable
            if (class_exists(Expense::class)) {
                Expense::create([
                    'date' => now(),
                    'concept' => 'Pago de Nómina Automática: ' . $payroll->name,
                    'amount' => $payroll->total_amount,
                    'reference' => 'NOM-' . str_pad($payroll->id, 5, '0', STR_PAD_LEFT),
                    'payment_method' => 'Transferencia',
                    'status' => 'Pagado',
                    'created_by' => auth()->id(),
                ]);

                if (class_exists(AccountingEntry::class)) {
                    $entry = AccountingEntry::create([
                        'date' => now(),
                        'reference' => 'NOM-' . $payroll->id,
                        'description' => 'Dispersión de Nómina: ' . $payroll->name,
                        'status' => 'Posteado',
                        'created_by' => auth()->id(),
                    ]);

                    AccountingEntryLine::create([
                        'accounting_entry_id' => $entry->id,
                        'accounting_account_id' => 52, // Sueldos y Salarios
                        'debit' => $payroll->total_amount,
                        'credit' => 0,
                    ]);

                    AccountingEntryLine::create([
                        'accounting_entry_id' => $entry->id,
                        'accounting_account_id' => 1, // Banco Principal
                        'debit' => 0,
                        'credit' => $payroll->total_amount,
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Nómina aprobada, dispersada e integrada a la contabilidad general.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar pago: ' . $e->getMessage());
        }
    }

    /**
     * Delete draft payroll lot.
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'Pagado') {
            return back()->with('error', 'No se puede eliminar una nómina ya pagada. Requiere reverso contable.');
        }

        $payroll->items()->delete();
        $payroll->delete();

        return back()->with('success', 'Lote de nómina eliminado correctamente.');
    }

    /**
     * Get items of a payroll lot.
     */
    public function items(Payroll $payroll)
    {
        $items = $payroll->items()->with(['employee.user'])->get()->map(fn($item) => [
            'id' => $item->id,
            'employee_name' => $item->employee?->user?->name ?? 'Empleado',
            'position' => $item->employee?->position ?? 'Sin cargo',
            'department' => $item->employee?->department ?? 'General',
            'contract_type' => $item->employee?->contract_type ?? 'Mensual',
            'base_amount' => (float)$item->base_amount,
            'deductions' => (float)$item->deductions,
            'net_amount' => (float)$item->net_amount,
            'details' => $item->details,
        ]);

        return response()->json([
            'payroll' => [
                'id' => $payroll->id,
                'name' => $payroll->name,
                'period' => $payroll->start_date?->format('d/m/Y') . ' - ' . $payroll->end_date?->format('d/m/Y'),
                'status' => $payroll->status,
                'total_amount' => (float)$payroll->total_amount,
            ],
            'items' => $items,
        ]);
    }
}
