<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Filament\Models\Contracts\HasName;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use phpDocumentor\Reflection\Types\Self_;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'password',
        'admin_create',
        'balance',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function canAccessFilament(): bool
    {
        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get all topups for the user.
     */
    public function topups()
    {
        return $this->hasMany(Topup::class);
    }

    /**
     * Check if user has enough balance for a given amount
     */
    public function hasEnoughBalance($amount)
    {
        return $this->balance >= $amount;
    }

    /**
     * Add balance to user account
     */
    public function addBalance($amount)
    {
        $this->balance += $amount;
        $this->save();
        
        return $this;
    }
}
