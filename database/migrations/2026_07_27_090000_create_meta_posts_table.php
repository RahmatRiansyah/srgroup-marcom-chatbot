<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini diisi lewat 2 jalur:
     * - service Python (meta_insights.py) yang tulis LANGSUNG lewat
     *   mysql-connector saat sync ke Graph API (pola sama seperti scraper.py
     *   menulis ke trend_posts).
     * - Laravel baca lewat Eloquent (Model MetaPost) untuk ditampilkan di
     *   dashboard/admin.
     *
     * Beda dari trend_posts (kompetitor, cuma caption/teks hasil scraping),
     * tabel ini simpan angka engagement ASLI dari akun Meta milik sendiri.
     */
    public function up(): void
    {
        Schema::create('meta_posts', function (Blueprint $table) {
            $table->id();
            $table->string('external_media_id')->unique(); // ID post dari Instagram
            $table->string('media_type')->nullable();         // IMAGE, VIDEO, CAROUSEL_ALBUM
            $table->string('media_product_type')->nullable(); // FEED, REELS, STORY
            $table->text('caption')->nullable();
            $table->string('permalink')->nullable();
            $table->text('media_url')->nullable();
            $table->timestamp('posted_at')->nullable();

            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('saved')->nullable();
            $table->unsignedInteger('shares')->nullable();
            $table->unsignedInteger('reach')->nullable();
            $table->unsignedInteger('views')->nullable();

            // (likes+comments+saved+shares) / reach * 100
            $table->decimal('engagement_rate_reach', 6, 2)->nullable();
            // (likes+comments+saved+shares) / followers_count * 100
            $table->decimal('engagement_rate_followers', 6, 2)->nullable();

            // Kapan data post ini terakhir ditarik dari Graph API -- dipakai
            // untuk menampilkan kesegaran data ("disinkron 12 menit lalu").
            $table->timestamp('fetched_at')->nullable();

            $table->timestamps();

            $table->index('posted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_posts');
    }
};