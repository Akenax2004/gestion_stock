<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\MustVerifyEmail; // Commenter ou supprimer cette ligne si la vérification d'email est désactivée
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne; // <--- AJOUTE OU VÉRIFIE CETTE IMPORTATION
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable // Ne plus implémenter MustVerifyEmail si désactivé
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'photo',
        'name',
        'username',
        'email',
        'password',
        'admin_principal_id',
        'company_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function scopeSearch($query, $value): void
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%");
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'user_id');
    }

    // Correction du type hint ici : utiliser Illuminate\Database\Eloquent\Relations\HasOne
    public function licence(): HasOne
    {
        return $this->hasOne(UserLicence::class, 'user_id');
    }

    public function adminPrincipal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_principal_id');
    }

    public function secondaryUsers(): HasMany
    {
        return $this->hasMany(User::class, 'admin_principal_id');
    }

    public function affiliatedCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function isAdminPrincipal(): bool
    {
        return $this->admin_principal_id === null && $this->hasRole('admin_principal');
    }

    public function isSecondaryUser(): bool
    {
        return $this->admin_principal_id !== null && ($this->hasRole('gestion') || $this->hasRole('vente'));
    }
}
