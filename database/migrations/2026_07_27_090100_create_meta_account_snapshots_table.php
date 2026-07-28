<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu baris = satu snapshot kondisi akun Meta saat sync dijalankan
     * (followers, jumlah post, rata-rata engagement rate post terbarunya).
     * Disimpan berkala (bukan di-update di tempat) supaya nanti bisa lihat
     * TREN follower/engagement dari waktu ke waktu, bukan cuma angka
     * terakhir.
     */
    public function up(): void
    {
        Schema::create('meta_account_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->unsignedInteger('followers_count')->nullable();
            $table->unsignedInteger('media_count')->nullable();
            $table->decimal('avg_engagement_rate', 6, 2)->nullable();
            $table->timestamp('snapshot_at');
            $table->timestamps();

            $table->index('snapshot_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_account_snapshots');
    }
};