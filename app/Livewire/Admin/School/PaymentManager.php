<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentPayment;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentManager extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterType = '';

    // Create/Edit modal
    public $showModal = false;
    public $editId = null;
    public $studentSearch = '';
    public $student_id = '';
    public $type = 'monthly';
    public $concept = '';
    public $amount = '';
    public $paid = 0;
    public $due_date = '';
    public $method = 'cash';
    public $receipt_number = '';
    public $notes = '';

    // Payment modal
    public $showPayModal = false;
    public $payId = null;
    public $payAmount = '';
    public $payMethod = 'cash';
    public $payReceipt = '';

    public function create()
    {
        $this->reset(['editId', 'student_id', 'type', 'concept', 'amount', 'paid', 'due_date', 'method', 'receipt_number', 'notes', 'studentSearch']);
        $this->type = 'monthly';
        $this->method = 'cash';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $p = StudentPayment::findOrFail($id);
        $this->editId = $p->id;
        $this->student_id = $p->student_id;
        $this->type = $p->type;
        $this->concept = $p->concept;
        $this->amount = $p->amount;
        $this->paid = $p->paid;
        $this->due_date = $p->due_date?->format('Y-m-d') ?? '';
        $this->method = $p->method ?? 'cash';
        $this->receipt_number = $p->receipt_number ?? '';
        $this->notes = $p->notes ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'student_id' => 'required|exists:students,id',
            'concept'    => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0',
        ]);

        $activeYear = AcademicYear::where('status', 'active')->first();
        $paid = floatval($this->paid);
        $amount = floatval($this->amount);

        $status = 'pending';
        if ($paid >= $amount) $status = 'paid';
        elseif ($paid > 0) $status = 'partial';

        StudentPayment::updateOrCreate(
            ['id' => $this->editId],
            [
                'student_id'       => $this->student_id,
                'academic_year_id' => $activeYear?->id,
                'type'             => $this->type,
                'concept'          => $this->concept,
                'amount'           => $amount,
                'paid'             => $paid,
                'status'           => $status,
                'due_date'         => $this->due_date ?: null,
                'method'           => $this->method,
                'receipt_number'   => $this->receipt_number ?: null,
                'notes'            => $this->notes ?: null,
                'paid_date'        => $paid >= $amount ? now() : null,
                'recorded_by'      => auth()->id(),
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->editId ? 'Pago actualizado.' : 'Pago registrado.');
    }

    public function openPay($id)
    {
        $p = StudentPayment::findOrFail($id);
        $this->payId = $id;
        $this->payAmount = $p->balance;
        $this->payMethod = $p->method ?? 'cash';
        $this->payReceipt = '';
        $this->showPayModal = true;
    }

    public function applyPayment()
    {
        $p = StudentPayment::findOrFail($this->payId);
        $newPaid = $p->paid + floatval($this->payAmount);

        $status = 'partial';
        if ($newPaid >= $p->amount) { $status = 'paid'; $newPaid = $p->amount; }

        $p->update([
            'paid'           => $newPaid,
            'status'         => $status,
            'method'         => $this->payMethod,
            'receipt_number' => $this->payReceipt ?: $p->receipt_number,
            'paid_date'      => $status === 'paid' ? now() : null,
        ]);

        $this->showPayModal = false;
        session()->flash('message', 'Pago aplicado: RD$ ' . number_format($this->payAmount, 2));
    }

    public function delete($id)
    {
        StudentPayment::findOrFail($id)->delete();
    }

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $payments = StudentPayment::query()
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->when($this->search, fn($q) =>
                $q->whereHas('student', fn($sq) =>
                    $sq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->with('student')
            ->orderByDesc('created_at')
            ->paginate(25);

        // Summary
        $totalDue = StudentPayment::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->sum('amount');
        $totalPaid = StudentPayment::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->sum('paid');
        $totalPending = $totalDue - $totalPaid;

        $studentResults = $this->studentSearch && strlen($this->studentSearch) >= 2
            ? Student::where('status', 'Activo')
                ->where(fn($q) =>
                    $q->where('first_name', 'like', "%{$this->studentSearch}%")
                      ->orWhere('last_name', 'like', "%{$this->studentSearch}%")
                )->limit(10)->get()
            : collect();

        return view('livewire.admin.school.payment-manager', [
            'payments'       => $payments,
            'types'          => StudentPayment::TYPES,
            'statuses'       => StudentPayment::STATUSES,
            'methods'        => StudentPayment::METHODS,
            'totalDue'       => $totalDue,
            'totalPaid'      => $totalPaid,
            'totalPending'   => $totalPending,
            'studentResults' => $studentResults,
        ])->layout('layouts.dashboard');
    }
}
