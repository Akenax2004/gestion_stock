<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     * Ajoute la colonne 'company_id' et sa clé étrangère à la table 'suppliers'.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('company_id')
                  ->nullable() // Rendez nullable si vous avez des données existantes
                  ->constrained('companies')
                  ->cascadeOnDelete();

            // Optionnel : Si 'name' ou 'email' doit être unique par entreprise
            // $table->unique(['email', 'company_id']);
            // $table->unique(['phone', 'company_id']);
        });
    }

    /**
     * Annule les migrations.
     * Supprime la colonne 'company_id' et sa clé étrangère de la table 'suppliers'.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Supprimer l'index unique composite si vous l'avez ajouté en 'up'
            // $table->dropUnique(['email', 'company_id']);
            // $table->dropUnique(['phone', 'company_id']);

            $table->dropConstrainedForeignId('company_id');
        });
    }
};
