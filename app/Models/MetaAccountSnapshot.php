<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaAccountSnapshot extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'snapshot_at' => 'datetime',
        'avg_engagement_rate' => 'float',
    ];
}