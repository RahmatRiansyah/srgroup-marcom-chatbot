<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Semua user baru default-nya role 'member' (lihat migrasi role/is_active).
 * Command ini yang dipakai buat jadiin akun PERTAMA sebagai admin, supaya
 * ada yang bisa buka /admin/users dan promosikan user lain lewat UI setelahnya.
 *
 * Pemakaian: php artisan user:make-admin email@kamu.com
 */
class MakeUserAdmin extends Command
{
    protected $signature = 'user:make-admin {email}';

    protected $description = 'Jadikan user (dicari lewat email) sebagai admin dan pastikan akunnya aktif.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User dengan email {$email} tidak ditemukan.");

            return self::FAILURE;
        }

        $user->update(['role' => 'admin', 'is_active' => true]);

        $this->info("{$user->name} ({$user->email}) sekarang admin.");

        return self::SUCCESS;
    }
}
