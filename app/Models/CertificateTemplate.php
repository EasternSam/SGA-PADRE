<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    protected $fillable = ['name', 'layout_data', 'is_active', 'background_image'];

    protected $casts = [
        'layout_data' => 'array',
        'is_active' => 'boolean',
    ];
}