<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'total_targets',
        'success_count',
        'failed_count',
        'message',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
