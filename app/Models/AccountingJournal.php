<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prefix',
    ];

    public function entries()
    {
        return $this->hasMany(AccountingEntry::class, 'accounting_journal_id');
    }
}
