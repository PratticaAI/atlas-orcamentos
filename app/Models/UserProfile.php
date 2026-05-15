<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'crea_cau',
        'phone',
        'logo_path',
        'accent_color',
        'city',
        'state',
        'about',
        'terms_accepted',
        'terms_accepted_at',
    ];

    protected $casts = [
        'terms_accepted'    => 'boolean',
        'terms_accepted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasLogo(): bool
    {
        return !empty($this->logo_path);
    }
}
