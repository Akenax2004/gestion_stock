<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Assurez-vous que Carbon est importé

class CompanyLicence extends Model // Conserve ce nom de classe
{
    use HasFactory;

    // Cette ligne force le modèle CompanyLicence à utiliser la table 'user_licences'
    protected $table = 'user_licences';

    protected $fillable = [
        'user_id',          // Important : la licence est liée à l'utilisateur
        'licence_plan_id',
        'start_date',
        'end_date',
        'status',
        'transaction_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_TRIAL = 'TRIAL';
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_CANCELLED = 'CANCELLED';

    /**
     * Une licence appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Une licence est associée à un plan de licence.
     */
    public function licencePlan()
    {
        return $this->belongsTo(LicencePlan::class);
    }

    /**
     * Accesseur pour vérifier si la licence est active et non expirée.
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return ($this->status === self::STATUS_ACTIVE || $this->status === self::STATUS_TRIAL)
               && ($this->end_date->isFuture() || $this->end_date->isToday());
    }

    /**
     * Marque la licence comme active.
     */
    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * Marque la licence comme expirée.
     */
    public function expire(): void
    {
        $this->status = self::STATUS_EXPIRED;
        $this->save();
    }
}
