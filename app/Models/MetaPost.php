<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaPost extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'posted_at' => 'datetime',
        'fetched_at' => 'datetime',
        'engagement_rate_reach' => 'float',
        'engagement_rate_followers' => 'float',
    ];

    /**
     * Angka interaksi total (like+komentar+saved+shares) -- dipakai di
     * tampilan admin/dashboard tanpa perlu hitung ulang di Blade.
     */
    public function getInteractionsAttribute(): int
    {
        return (int) $this->likes + (int) $this->comments + (int) ($this->saved ?? 0) + (int) ($this->shares ?? 0);
    }
}