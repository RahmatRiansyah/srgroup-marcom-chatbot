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
        'engine',
        'tool_calls',
    ];

    // tool_calls disimpan sebagai JSON di DB, otomatis di-decode jadi array
    // PHP saat dibaca (dipakai fitur Auto-Chart di resources/views/chat/index.blade.php)
    protected $casts = [
        'tool_calls' => 'array',
    ];

    // Relasi balik ke data User yang sedang login
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}