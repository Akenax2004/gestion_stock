<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // <--- AJOUTEZ CETTE LIGNE

class UserLicence extends Model
{
    use HasFactory;

    // Nom de la table si elle ne suit pas la convention de nommage Laravel (pluriel du modèle)
    protected $table = 'user_licences';

    protected $fillable = [
        'user_id',
        'licence_plan_id',
        'start_date',
        'end_date',
        'status',
        'transaction_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // --- DÉBUT DES CONSTANTES DE STATUT (NOUVELLES) ---
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVE = 'INACTIVE';
    public const STATUS_TRIAL = 'TRIAL';
    public const STATUS_PENDING = 'PENDING'; // Pour les paiements en attente, par exemple
    public const STATUS_EXPIRED = 'EXPIRED'; // Un statut pour les licences expirées
    // --- FIN DES CONSTANTES DE STATUT ---

    // Relation avec l'utilisateur qui possède cette licence
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec le plan de licence associé
    public function licencePlan(): BelongsTo
    {
        return $this->belongsTo(LicencePlan::class);
    }

    /**
     * Vérifie si la licence est active et non expirée.
     * Une licence active inclut aussi un essai gratuit valide.
     */
    public function isActive(): bool
    {
        // Assurez-vous que end_date n'est pas null avant de l'analyser
        return ($this->status === self::STATUS_ACTIVE || $this->status === self::STATUS_TRIAL)
               && $this->end_date && Carbon::parse($this->end_date)->isFuture();
    }
}
