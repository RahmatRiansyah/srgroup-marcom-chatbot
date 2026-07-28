<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'posts_synced',
        'posts_failed',
        'message',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}