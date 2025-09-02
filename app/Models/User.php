<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasProfilePhoto;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
    ];

    public const ROLE_SUPER_ADMIN = 'super admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_KASIR = 'kasir';
    public const ROLE_PASSET_BANTU = 'passet bantu';

    public function isSuperAdmin(): bool
    {
        return trim($this->role) === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return trim($this->role) === self::ROLE_ADMIN;
    }

    public function isKasir(): bool
    {
        return trim($this->role) === self::ROLE_KASIR;
    }

    public function isPassetBantu(): bool
    {
        return trim($this->role) === self::ROLE_PASSET_BANTU;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the branch that the user belongs to
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the branches managed by this user
     */
    public function managedBranches()
    {
        return $this->hasMany(Branch::class, 'manager_id');
    }

    /**
     * Check if user can access multiple branches (Super Admin)
     */
    public function canAccessAllBranches(): bool
    {
        return trim($this->role) === self::ROLE_SUPER_ADMIN || trim($this->role) === self::ROLE_ADMIN;
    }

    /**
     * Get user's accessible branches
     */
    public function getAccessibleBranches()
    {
        if ($this->canAccessAllBranches()) {
            return Branch::active()->get();
        }
        
        return $this->branch ? collect([$this->branch]) : collect();
    }
}
