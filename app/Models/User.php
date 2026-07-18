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
    public const ADMIN = 'ADMIN';

    public const STOCK_STAFF = 'STOCK_STAFF';

    public const VIEWER = 'VIEWER';

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'last_login_at', 'must_change_password'];

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN;
    }

    public function canOperateStock(): bool
    {
        return in_array($this->role, [self::ADMIN, self::STOCK_STAFF], true);
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class, 'requested_by');
    }

    public function signature()
    {
        return $this->hasOne(UserSignature::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'is_active' => 'boolean', 'last_login_at' => 'datetime', 'must_change_password' => 'boolean',
        ];
    }
}
