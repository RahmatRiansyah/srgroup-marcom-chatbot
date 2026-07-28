<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catat engine AI mana (claude/groq/gemini) yang benar-benar menjawab
     * tiap pesan -- sebelumnya cuma bisa dilihat real-time (badge di UI chat),
     * tidak pernah disimpan permanen. Nullable karena data lama tidak punya
     * info ini, dan karena bisa jadi 'none' kalau semua engine gagal/limit
     * (disimpan sebagai NULL, bukan string 'none', biar gampang di-query).
     */
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('engine', 20)->nullable()->after('response');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('engine');
        });
    }
};
