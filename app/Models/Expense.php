<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'reference_number',
        'ncf',
        'expense_type_606',
        'expense_date',
        'due_date',
        'expense_account_id',
        'payment_account_id',
        'subtotal',
        'itbis_amount',
        'itbis_retained',
        'isr_retained',
        'total_amount',
        'status',
        'description',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'itbis_amount' => 'decimal:2',
        'itbis_retained' => 'decimal:2',
        'isr_retained' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relación con el Suplidor
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relación con la cuenta de Gasto (Debe)
    public function expenseAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'expense_account_id');
    }

    // Relación con la cuenta de Pago o Pasivo (Haber)
    public function paymentAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'payment_account_id');
    }

    // Relación polimórfica para obtener su asiento contable
    public function accountingEntries()
    {
        return $this->morphMany(AccountingEntry::class, 'reference');
    }
}
