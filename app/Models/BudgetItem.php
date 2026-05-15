<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    protected $fillable = [
        'budget_id',
        'sinapi_code',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        'source',
        'sort_order',
    ];

    protected $casts = [
        'quantity'    => 'float',
        'unit_price'  => 'float',
        'total_price' => 'float',
        'sort_order'  => 'integer',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function sourceLabel(): string
    {
        return match($this->source) {
            'sinapi'    => 'SINAPI',
            'ai'        => 'IA',
            'manual'    => 'Manual',
            'estimated' => 'Estimado',
            default     => $this->source,
        };
    }
}
