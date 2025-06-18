<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licence_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Basique Mensuel"
            $table->string('plan_type'); // Ex: 'BASIC', 'STANDARD', 'PREMIUM', 'TRIAL'
            $table->string('duration_type'); // Ex: 'MONTHLY', 'ANNUAL', 'TRIAL'
            $table->integer('duration_days')->comment('Durée du plan en jours (ex: 30, 90, 365)');
            $table->decimal('price_xof', 10, 0); // Prix en XOF
            $table->json('features')->nullable(); // Liste des fonctionnalités incluses
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licence_plans');
    }
};