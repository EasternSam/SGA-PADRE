<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'rnc_cedula',
        'name',
        'type',
        'phone',
        'email',
    ];
}
