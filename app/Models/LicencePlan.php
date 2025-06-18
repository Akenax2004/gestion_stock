<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicencePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'plan_type', 'duration_type', 'duration_days', 'price_xof', 'features', 'max_companies',
    ];

    protected $casts = [
        'features' => 'array', // Pour que Laravel gère le JSON automatiquement
        'max_companies' => 'integer',
    ];

    // Définir des constantes pour les types de plan si vous le souhaitez
    public const PLAN_BASIC = 'BASIC';
    public const PLAN_STANDARD = 'STANDARD';
    public const PLAN_PREMIUM = 'PREMIUM';
    public const PLAN_TRIAL = 'TRIAL';

    public const DURATION_MONTHLY = 'MONTHLY';
    public const DURATION_QUARTERLY = 'QUARTERLY';
    public const DURATION_ANNUAL = 'ANNUAL';
    public const DURATION_TRIAL = 'TRIAL';
}