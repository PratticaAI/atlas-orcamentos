<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price_cents',
        'budget_limit',
        'user_limit',
        'features',
        'pagarme_plan_id',
        'active',
    ];

    protected $casts = [
        'features'     => 'array',
        'active'       => 'boolean',
        'budget_limit' => 'integer',
        'user_limit'   => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function priceFormatted(): string
    {
        return 'R$ ' . number_format($this->price_cents / 100, 2, ',', '.');
    }

    public function isUnlimited(): bool
    {
        return is_null($this->budget_limit);
    }
}
