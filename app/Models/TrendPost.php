<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendPost extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke model TrendSource
    public function trendSource()
    {
        return $this->belongsTo(TrendSource::class);
    }
}