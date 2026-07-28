<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log tiap kali sync ke Meta Graph API dijalankan -- pola sama persis
     * dengan scrape_logs (untuk kompetitor), supaya admin bisa lihat riwayat
     * & error di satu tempat yang konsisten.
     */
    public function up(): void
    {
        Schema::create('meta_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('status'); // success | partial | failed
            $table->unsignedInteger('posts_synced')->default(0);
            $table->unsignedInteger('posts_failed')->default(0);
            $table->text('message')->nullable(); // pesan error, mis. token expired
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_sync_logs');
    }
};