<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     * Crée la table 'companies' avec les champs de base et une liaison à l'utilisateur.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id(); // Colonne ID auto-incrémentée
            
            // Liaison à l'utilisateur : chaque entreprise appartient à un utilisateur
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users') // La table des utilisateurs est 'users'
                ->cascadeOnDelete(); // Si un utilisateur est supprimé, ses entreprises sont aussi supprimées

            $table->string('name')->unique(); // Nom de l'entreprise, doit être unique globalement (ou par user, voir note)
            // Note: Si vous voulez que le nom soit unique PAR UTILISATEUR, la contrainte unique
            // devrait être ajoutée via une migration séparée comme nous l'avons fait pour les unités,
            // avec un index composite (name, user_id). Pour l'instant, c'est unique globalement.
            
            $table->string('email')->unique()->nullable(); // Email unique et optionnel
            $table->string('phone')->unique()->nullable(); // Téléphone unique et optionnel
            $table->string('address')->nullable(); // Adresse optionnelle
            $table->string('vat_number')->nullable(); // Numéro de TVA optionnel
            $table->string('logo')->nullable(); // Chemin vers le logo optionnel
            $table->boolean('is_active')->default(true); // Statut actif par défaut

            $table->timestamps(); // created_at et updated_at
        });
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
