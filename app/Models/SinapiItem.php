<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinapiItem extends Model
{
    protected $fillable = [
        'code',
        'description',
        'unit',
        'state',
        'price',
        'reference_month',
        'deonerado',
    ];

    protected $casts = [
        'price'     => 'float',
        'deonerado' => 'boolean',
    ];
}
