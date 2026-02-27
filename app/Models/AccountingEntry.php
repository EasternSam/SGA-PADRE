<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'accounting_journal_id',
        'date',
        'reference_id',
        'reference_type',
        'description',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function journal()
    {
        return $this->belongsTo(AccountingJournal::class, 'accounting_journal_id');
    }

    public function lines()
    {
        return $this->hasMany(AccountingEntryLine::class, 'accounting_entry_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
