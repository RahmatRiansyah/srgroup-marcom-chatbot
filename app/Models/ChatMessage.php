<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    // Mengizinkan kolom-kolom ini diisi data
    protected $fillable = [
        'user_id',
        'chat_session_id',
        'message',
        'response',
    ];

    // Relasi balik ke data User yang sedang login
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}