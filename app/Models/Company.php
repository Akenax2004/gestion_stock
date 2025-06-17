<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;

    // Les champs qui peuvent être assignés en masse
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'vat_number',
        'logo',
        'is_active',
    ];

    // Définition de la relation avec le modèle User
    // Une entreprise appartient à un utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Vous pourriez ajouter d'autres relations ici, par exemple :
    // Une entreprise peut avoir plusieurs produits, clients, commandes, etc.
    // public function products(): HasMany
    // {
    //     return $this->hasMany(Product::class);
    // }
}
