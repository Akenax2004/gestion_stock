<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LicencePlan;

class LicencePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan d'essai gratuit
        LicencePlan::firstOrCreate(
            ['plan_type' => LicencePlan::PLAN_TRIAL],
            [
                'name' => 'Essai Gratuit (30 jours)',
                'duration_type' => LicencePlan::DURATION_TRIAL,
                'duration_days' => 30, // 30 jours d'essai
                'price_xof' => 0,
                'max_companies' => 3, // Exemple: l'essai permet de gérer 3 entreprises
                'features' => [
                    'Toutes les fonctionnalités de base',
                    'Jusqu\'à 3 entreprises',
                    'Rapports standards'
                ],
            ]
        );

        // Vos autres plans payants
        LicencePlan::firstOrCreate(
            ['name' => 'Basique Mensuel'],
            [
                'plan_type' => LicencePlan::PLAN_BASIC,
                'duration_type' => LicencePlan::DURATION_MONTHLY,
                'duration_days' => 30,
                'price_xof' => 5000,
                'max_companies' => 1,
                'features' => ['Gestion de stock de base', '1 entreprise', 'Support par email'],
            ]
        );

        LicencePlan::firstOrCreate(
            ['name' => 'Standard Annuel'],
            [
                'plan_type' => LicencePlan::PLAN_STANDARD,
                'duration_type' => LicencePlan::DURATION_ANNUAL,
                'duration_days' => 365,
                'price_xof' => 150000,
                'max_companies' => 10,
                'features' => ['Toutes les fonctionnalités Basiques', 'Jusqu\'à 10 entreprises', 'Alertes avancées', 'Rapports détaillés', 'Support prioritaire'],
            ]
        );

        LicencePlan::firstOrCreate(
            ['name' => 'Premium Annuel'],
            [
                'plan_type' => LicencePlan::PLAN_PREMIUM,
                'duration_type' => LicencePlan::DURATION_ANNUAL,
                'duration_days' => 365,
                'price_xof' => 400000,
                'max_companies' => -1, // Illimité
                'features' => ['Toutes les fonctionnalités Standard', 'Nombre illimité d\'entreprises', 'Intégrations API', 'Personnalisation', 'Formation dédiée', 'Support 24/7'],
            ]
        );
    }
}
