<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'platform',
        'source_url',
        'is_active',
    ];
}