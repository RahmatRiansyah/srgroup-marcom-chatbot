<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trend_posts', function (Blueprint $table) {
            $table->id();
            // Terhubung ke tabel trend_sources (target/kompetitor)
            $table->foreignId('trend_source_id')->constrained('trend_sources')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('content'); // Teks postingan / caption / rangkuman tren
            $table->string('post_url')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trend_posts');
    }
};
