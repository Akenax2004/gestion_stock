<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Ajouté pour la relation users

class Company extends Model
{
    use HasFactory;

    // Les champs qui peuvent être assignés en masse
    protected $fillable = [
        'user_id',      // L'ID de l'admin principal qui possède cette entreprise
        'name',
        'email',
        'phone',
        'address',
        'vat_number',
        'logo',
        'is_active',
    ];

    // Définition de la relation avec le modèle User
    // Une entreprise appartient à un utilisateur (admin_principal)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Une entreprise peut avoir plusieurs utilisateurs secondaires rattachés
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id');
    }

    // Vous pourriez ajouter d'autres relations ici, par exemple :
    // Une entreprise peut avoir plusieurs catégories, produits, clients, commandes, etc.
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'company_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'company_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'company_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'company_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'company_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'company_id');
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'company_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'company_id');
    }
}