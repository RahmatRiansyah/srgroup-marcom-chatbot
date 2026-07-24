<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'email_verified_at',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_expires_at' => 'datetime',
        ];
    }

    /** Dipakai middleware EnsureUserIsAdmin & di view (sidebar, dsb) buat cek akses admin. */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Generate 6-digit OTP code and expiration time (10 mins).
     */
    public function generateTwoFactorCode(): string
    {
        $code = (string) rand(100000, 999999);
        $this->timestamps = false;
        $this->two_factor_code = $code;
        $this->two_factor_expires_at = now()->addMinutes(10);
        $this->save();

        return $code;
    }

    /**
     * Clear OTP code and expiration date.
     */
    public function resetTwoFactorCode(): void
    {
        $this->timestamps = false;
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }

    /**
     * Check if given OTP code matches and is not expired.
     */
    public function isTwoFactorCodeValid(string $code): bool
    {
        if (empty($this->two_factor_code) || empty($this->two_factor_expires_at)) {
            return false;
        }

        return $this->two_factor_code === trim($code) && $this->two_factor_expires_at->gt(now());
    }
}
