<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(AccountingAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccountingAccount::class, 'parent_id');
    }

    public function entryLines()
    {
        return $this->hasMany(AccountingEntryLine::class, 'accounting_account_id');
    }
}
