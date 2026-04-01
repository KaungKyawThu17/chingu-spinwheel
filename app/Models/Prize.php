<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'weight',
        'color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];
}
