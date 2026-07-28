<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simpan detail tool_calls (nama tool, input, dan result mentah dari
     * AnalyticsApiService) per pesan AI -- sebelumnya cuma dikirim ke
     * frontend untuk transparansi lalu dibuang (lihat komentar lama di
     * ChatController::send()), tidak pernah disimpan.
     *
     * Sekarang disimpan supaya fitur Auto-Chart (render grafik dari data
     * getSummary/getEngagement/getMetaPosts/getGoogleTrendsNow) tetap
     * muncul saat user reload halaman / buka riwayat sesi lama, bukan cuma
     * saat respons live pertama kali diterima.
     *
     * Nullable + JSON: data lama tidak punya ini (NULL = "tidak ada data
     * tool untuk pesan ini", beda dari array kosong "tool dipanggil tapi
     * hasilnya kosong").
     */
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->json('tool_calls')->nullable()->after('engine');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('tool_calls');
        });
    }
};
