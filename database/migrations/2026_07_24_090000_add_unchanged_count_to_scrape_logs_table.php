<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: sebelumnya angka "unchanged" (target yang berhasil diproses tapi
 * kontennya sama seperti hasil scrape terakhir, jadi sengaja tidak di-insert
 * duplikat -- lihat scraper.py get_last_content_hash()) dari mesin Python
 * dibuang begitu saja oleh RunScrape.php, tidak pernah dicatat ke scrape_logs.
 *
 * Akibatnya, Total Target vs (Berhasil + Gagal) di tabel admin/scrape-log
 * sering tidak sinkron -- misal 5 target diproses tapi kolom Berhasil &
 * Gagal sama-sama menunjukkan 0, padahal sebenarnya semuanya sudah dicek
 * dan aman (cuma tidak ada konten baru). Kolom ini menutup celah itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scrape_logs', function (Blueprint $table) {
            $table->unsignedInteger('unchanged_count')->default(0)->after('success_count');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_logs', function (Blueprint $table) {
            $table->dropColumn('unchanged_count');
        });
    }
};
