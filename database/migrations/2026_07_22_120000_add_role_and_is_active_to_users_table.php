<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Roadmap Minggu 7: "Kelola user yang boleh akses chatbot (role/permission)".
     *
     * - role: 'admin' bisa buka /admin/* (kelola data source, log scraping, kelola user).
     *         'member' cuma bisa pakai dashboard & chatbot.
     * - is_active: dipakai admin buat menonaktifkan akses chatbot seseorang
     *   tanpa perlu hapus akunnya (mis. karyawan resign/cuti panjang).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('member')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
