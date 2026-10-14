<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements PasskeyUser
{
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'role',
        'balance',
        'password',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'user_packages')
            ->withPivot(['id', 'remaining_minutes', 'purchased_at', 'expired_at'])
            ->withTimestamps();
    }

    public function seatSessions()
    {
        return $this->hasMany(SeatSession::class);
    }

    public function activeSession()
    {
        return $this->hasOne(SeatSession::class)->where('status', 'active')->latestOfMany();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['staff', 'admin']);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ตัดย่อชื่อผู้ใช้ไว้แสดงใน avatar
    public function initials(): string
    {
        $name = trim($this->name ?: $this->username ?: 'U');
        $words = explode(' ', $name);

        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 1));
    }
}
