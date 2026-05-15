<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'work_type',
        'area_m2',
        'standard',
        'state',
        'description',
        'bdi_percent',
        'subtotal',
        'bdi_value',
        'total',
        'pdf_path',
        'status',
        'ai_model',
    ];

    protected $casts = [
        'area_m2'     => 'float',
        'bdi_percent' => 'float',
        'subtotal'    => 'float',
        'bdi_value'   => 'float',
        'total'       => 'float',
    ];

    // ------------------------------------------------------------------
    // Relacionamentos
    // ------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class)->orderBy('sort_order');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    public function workTypeLabel(): string
    {
        return match($this->work_type) {
            'residential' => 'Residencial',
            'commercial'  => 'Comercial',
            'renovation'  => 'Reforma',
            'industrial'  => 'Industrial / Galpão',
            default       => $this->work_type,
        };
    }

    public function standardLabel(): string
    {
        return match($this->standard) {
            'simple' => 'Simples',
            'medium' => 'Médio',
            'high'   => 'Alto',
            default  => $this->standard,
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'draft'     => 'Rascunho',
            'generated' => 'Gerado',
            'exported'  => 'Exportado',
            default     => $this->status,
        };
    }

    public function hasPdf(): bool
    {
        return !empty($this->pdf_path);
    }
}
