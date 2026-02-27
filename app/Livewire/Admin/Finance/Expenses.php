<?php

namespace App\Livewire\Admin\Finance;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\Expense;
use App\Services\AccountingEngine;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Expenses extends Component
{
    use WithPagination;

    // View state
    public $showModal = false;
    public $showPayModal = false;
    public $search = '';
    public $status_filter = '';

    // Form inputs
    public $expense_id;
    public $supplier_id = '';
    public $reference_number = '';
    public $ncf = '';
    public $expense_type_606 = '02';
    public $expense_date = '';
    public $due_date = '';
    public $expense_account_id = '';
    public $payment_account_id = '';
    public $subtotal = 0;
    public $itbis_amount = 0;
    public $itbis_retained = 0;
    public $isr_retained = 0;
    public $total_amount = 0;
    public $description = '';
    public $status = 'paid';

    // Pay Modal
    public $expense_to_pay = null;
    public $pay_account_origin_id = '';
    
    // Selections
    public $expenseAccounts = [];
    public $paymentAccounts = [];
    public $assetAccounts = []; // Para saldar (Solo Cajas / Bancos)
    public $journals = [];
    public $suppliers = [];
    public $selected_journal_id = '';
    public $expenseTypes606 = [
        '01' => 'Gastos en personal',
        '02' => 'Gastos por trabajos, suministros y servicios',
        '03' => 'Arrendamientos',
        '04' => 'Gastos de activos fijos',
        '05' => 'Gastos de representación',
        '06' => 'Otras deducciones admitidas',
        '07' => 'Gastos financieros',
        '08' => 'Gastos extraordinarios',
        '09' => 'Compras/Gastos parte del costo',
        '10' => 'Adquisiciones de activos',
        '11' => 'Gastos de seguros',
    ];

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'reference_number' => 'nullable|string|max:100',
        'ncf' => 'nullable|string|max:19',
        'expense_type_606' => 'required|string|size:2',
        'expense_date' => 'required|date',
        'due_date' => 'nullable|date',
        'expense_account_id' => 'required|exists:accounting_accounts,id',
        'payment_account_id' => 'required|exists:accounting_accounts,id',
        'subtotal' => 'required|numeric|min:0.01',
        'itbis_amount' => 'nullable|numeric|min:0',
        'itbis_retained' => 'nullable|numeric|min:0',
        'isr_retained' => 'nullable|numeric|min:0',
        'total_amount' => 'required|numeric|min:0',
        'status' => 'required|in:paid,pending,void',
        'selected_journal_id' => 'required|exists:accounting_journals,id',
    ];

    public function mount()
    {
        $this->loadAccounts();
        $this->journals = AccountingJournal::all();
        $this->suppliers = \App\Models\Supplier::orderBy('name')->get();
        // pre-select first journal
        if ($this->journals->count() > 0) {
            $this->selected_journal_id = $this->journals->first()->id;
        }
        $this->expense_date = date('Y-m-d');
    }

    public function loadAccounts()
    {
        // Gastos: Todo lo que sea 'expense'
        $this->expenseAccounts = AccountingAccount::where('type', 'expense')->where('is_active', true)->orderBy('code')->get();
        // Pago Inicial: Activos (Caja/Bancos) o Pasivos (Cuentas por Pagar)
        $this->paymentAccounts = AccountingAccount::whereIn('type', ['asset', 'liability'])->where('is_active', true)->orderBy('code')->get();
        // Saldar Factura: Solo activos (De donde sale el dinero: Caja/Banco)
        $this->assetAccounts = AccountingAccount::where('type', 'asset')->where('is_active', true)->orderBy('code')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['expense_id', 'supplier_id', 'reference_number', 'ncf', 'due_date', 'expense_account_id', 'payment_account_id', 'subtotal', 'itbis_amount', 'itbis_retained', 'isr_retained', 'total_amount', 'description']);
        $this->expense_date = date('Y-m-d');
        $this->status = 'paid';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function calculateTotal()
    {
        $sub = (float)($this->subtotal ?: 0);
        $itbis = (float)($this->itbis_amount ?: 0);
        $retItbis = (float)($this->itbis_retained ?: 0);
        $retIsr = (float)($this->isr_retained ?: 0);

        $this->total_amount = $sub + $itbis - $retItbis - $retIsr;
    }

    public function updatedSubtotal() { $this->calculateTotal(); }
    public function updatedItbisAmount() { $this->calculateTotal(); }
    public function updatedItbisRetained() { $this->calculateTotal(); }
    public function updatedIsrRetained() { $this->calculateTotal(); }

    public function save(AccountingEngine $engine)
    {
        $this->calculateTotal();
        $this->validate();

        try {
            DB::beginTransaction();

            // 1. Crear el Gasto (Expense Módulo Local)
            $expense = Expense::create([
                'supplier_id' => $this->supplier_id,
                'reference_number' => $this->reference_number,
                'ncf' => $this->ncf,
                'expense_type_606' => $this->expense_type_606,
                'expense_date' => $this->expense_date,
                'due_date' => $this->due_date,
                'expense_account_id' => $this->expense_account_id,
                'payment_account_id' => $this->payment_account_id,
                'subtotal' => $this->subtotal,
                'itbis_amount' => $this->itbis_amount ?: 0,
                'itbis_retained' => $this->itbis_retained ?: 0,
                'isr_retained' => $this->isr_retained ?: 0,
                'total_amount' => $this->total_amount,
                'status' => $this->status,
                'description' => $this->description,
            ]);

            // 2. Integrar al Motor Contable de Partida Doble
            // Delega la complejidad impositiva (retenciones) al motor contable "The God Tier"
            $engine->registerExpense($expense, $this->selected_journal_id);

            DB::commit();

            session()->flash('success', 'Gasto registrado y contabilizado exitosamente en el Libro Mayor.');
            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al contabilizar el gasto: ' . $e->getMessage());
        }
    }

    public function openPayModal($expenseId)
    {
        $this->expense_to_pay = Expense::with('supplier', 'paymentAccount')->find($expenseId);
        if ($this->expense_to_pay && $this->expense_to_pay->status === 'pending') {
            $this->pay_account_origin_id = ''; 
            $this->showPayModal = true;
        }
    }

    public function closePayModal()
    {
        $this->showPayModal = false;
        $this->expense_to_pay = null;
    }

    public function processPayment(AccountingEngine $engine)
    {
        $this->validate([
            'pay_account_origin_id' => 'required|exists:accounting_accounts,id',
            'selected_journal_id' => 'required|exists:accounting_journals,id',
        ], [
            'pay_account_origin_id.required' => 'Debe seleccionar una cuenta de origen (Caja/Banco).',
            'selected_journal_id.required' => 'Debe seleccionar un Diario contable.'
        ]);

        if (!$this->expense_to_pay || $this->expense_to_pay->status !== 'pending') {
            return;
        }

        try {
            DB::beginTransaction();

            $expense = $this->expense_to_pay;
            $monto = $expense->total_amount;
            
            // Asiento de pago
            $lines = [
                [
                    'account_id' => $expense->payment_account_id, // DEBITO a la CxP (Disminuye el Pasivo)
                    'debit' => $monto,
                    'credit' => 0,
                    'desc' => "Saldando CxP Factura Suplidor: " . ($expense->supplier->name ?? 'N/A')
                ],
                [
                    'account_id' => $this->pay_account_origin_id, // CREDITO a Caja/Banco (Disminuye el Activo)
                    'debit' => 0,
                    'credit' => $monto,
                    'desc' => "Pago de Factura (Ref: {$expense->reference_number})"
                ]
            ];

            $desc = "Pago de Factura Suplidor: " . ($expense->supplier->name ?? 'N/A');
            $engine->makeEntry($this->selected_journal_id, date('Y-m-d'), $desc, $expense, $lines);

            $expense->update(['status' => 'paid']);

            DB::commit();

            session()->flash('success', 'Cuenta por Pagar saldada exitosamente. Asiento generado en el Libro Mayor.');
            $this->closePayModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Expense::query()->with(['expenseAccount', 'paymentAccount']);

        if ($this->search) {
            $query->whereHas('supplier', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('rnc_cedula', 'like', '%' . $this->search . '%');
            })
            ->orWhere('reference_number', 'like', '%' . $this->search . '%')
            ->orWhere('ncf', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%');
        }

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }

        $expenses = $query->latest('expense_date')->paginate(10);

        return view('livewire.admin.finance.expenses', [
            'expenses' => $expenses
        ]);
    }
}
