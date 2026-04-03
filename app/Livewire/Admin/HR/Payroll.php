<?php

namespace App\Livewire\Admin\HR;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Payroll as PayrollModel;
use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\Expense; // For accounting integration
use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.dashboard')]
class Payroll extends Component
{
    use WithPagination;

    public $isModalOpen = false;
    public $name = '';
    public $start_date = '';
    public $end_date = '';
    public $cycle_type = 'Quincenal'; // Quincenal or Mensual

    public function create()
    {
        $this->name = 'Nómina ' . Carbon::now()->translatedFormat('F Y');
        $this->start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->end_date = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->dispatch('open-modal', 'payroll-modal');
    }

    public function generate()
    {
        $this->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cycle_type' => 'required|in:Quincenal,Mensual',
        ]);

        DB::beginTransaction();
        try {
            $payroll = PayrollModel::create([
                'name' => $this->name,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => 'Borrador',
                'total_amount' => 0
            ]);

            $employees = Employee::where('status', 'Activo')->get();
            $totalPayroll = 0;

            foreach ($employees as $emp) {
                $baseAmount = 0;
                $details = [];

                if ($emp->contract_type === 'Mensual') {
                    $baseAmount = $this->cycle_type === 'Quincenal' ? ($emp->base_salary / 2) : $emp->base_salary;
                    $details['formula'] = $this->cycle_type === 'Quincenal' ? 'Salario Base / 2' : 'Salario Base completo';
                } else {
                    // Por horas: Contamos los "Check-in" (punch_type = 0) en ese rango
                    $daysWorked = $emp->attendances()
                        ->whereBetween('punch_time', [$this->start_date . ' 00:00:00', $this->end_date . ' 23:59:59'])
                        ->where('punch_type', 0)
                        ->count();

                    // Asumimos un bloque de 4 horas por día impartido en promedio
                    $hoursWorked = $daysWorked * 4;
                    $baseAmount = $hoursWorked * $emp->hourly_rate;
                    
                    $details['days_worked'] = $daysWorked;
                    $details['estimated_hours'] = $hoursWorked;
                    $details['hourly_rate'] = $emp->hourly_rate;
                    $details['formula'] = "$hoursWorked hrs * RD$ {$emp->hourly_rate}";
                }

                // Cálculo de Deducciones de Ley (SFS 3.04%, AFP 2.87%) => 5.91%
                $deductions = $baseAmount * 0.0591;
                $netAmount = $baseAmount - $deductions;

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $emp->id,
                    'base_amount' => $baseAmount,
                    'deductions' => $deductions,
                    'net_amount' => $netAmount,
                    'details' => $details
                ]);

                $totalPayroll += $netAmount;
            }

            $payroll->update(['total_amount' => $totalPayroll]);

            DB::commit();
            session()->flash('success', 'Lote de nómina generado con ' . $employees->count() . ' empleados procesados calculando horarios.');
            $this->dispatch('close-modal', 'payroll-modal');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al generar nómina: ' . $e->getMessage());
        }
    }

    public function approveAndPay($id)
    {
        $payroll = PayrollModel::findOrFail($id);
        
        if ($payroll->status !== 'Borrador') {
            session()->flash('error', 'Esta nómina ya fue procesada.');
            return;
        }

        DB::beginTransaction();
        try {
            $payroll->update(['status' => 'Pagado']);

            // PHASE 4: Integración Financiera
            if (class_exists(Expense::class)) {
                $expense = Expense::create([
                    'date' => now(),
                    'concept' => 'Pago de Nómina Automática: ' . $payroll->name,
                    'amount' => $payroll->total_amount,
                    'reference' => 'NOM-' . str_pad($payroll->id, 5, '0', STR_PAD_LEFT),
                    'payment_method' => 'Transferencia',
                    'status' => 'Pagado',
                    'created_by' => auth()->id()
                ]);

                // Crear Asiento Contable Automático si el Ledger existe
                if (class_exists(AccountingEntry::class)) {
                    $entry = AccountingEntry::create([
                        'date' => now(),
                        'reference' => 'NOM-' . $payroll->id,
                        'description' => 'Pago de Nómina: ' . $payroll->name,
                        'status' => 'Posteado',
                        'created_by' => auth()->id()
                    ]);

                    // Debit to Gasto de Salarios (Assuming ID 52 - Sueldos y Salarios)
                    AccountingEntryLine::create([
                        'accounting_entry_id' => $entry->id,
                        'accounting_account_id' => 52, // Replace with real ID or query
                        'debit' => $payroll->total_amount,
                        'credit' => 0,
                    ]);

                    // Credit to Banco (Assuming ID 1)
                    AccountingEntryLine::create([
                        'accounting_entry_id' => $entry->id,
                        'accounting_account_id' => 1,
                        'debit' => 0,
                        'credit' => $payroll->total_amount,
                    ]);
                }
            }

            DB::commit();
            session()->flash('success', 'Nómina Pagada y Registrada contablemente en los gastos Institucionales.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error en el pago: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $payroll = PayrollModel::findOrFail($id);
        if ($payroll->status === 'Pagado') {
            session()->flash('error', 'No se puede eliminar una nómina ya pagada. Se requiere reverso contable.');
            return;
        }
        $payroll->delete();
        session()->flash('success', 'Nómina en borrador eliminada.');
    }

    public function render(): View
    {
        return view('livewire.admin.h-r.payroll', [
            'payrolls' => PayrollModel::withCount('items')->orderByDesc('id')->paginate(10)
        ]);
    }
}
