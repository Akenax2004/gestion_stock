<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Met à jour les contraintes d'unicité de la table 'units' pour inclure 'user_id'.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // Supprime les index uniques existants sur 'name' et 'slug'.
            // Les noms d'index par défaut de Laravel sont souvent 'tablename_columnname_unique'.
            $table->dropUnique(['name']);
            $table->dropUnique(['slug']);

            // Ajoute de nouveaux index uniques composites qui incluent 'user_id'.
            // Cela permet aux champs 'name' et 'slug' d'être uniques uniquement
            // pour les unités appartenant au même utilisateur.
            $table->unique(['name', 'user_id']);
            $table->unique(['slug', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     * Annule les modifications apportées dans la méthode 'up()'.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // Supprime les nouveaux index uniques composites
            $table->dropUnique(['name', 'user_id']);
            $table->dropUnique(['slug', 'user_id']);

            // Recrée les anciens index uniques (si vous voulez revenir à l'état précédent)
            // Note: Si la colonne 'name' ou 'slug' contient déjà des doublons GLOBALES,
            // cette étape 'down' échouera. C'est pourquoi il est crucial de gérer les données.
            $table->unique('name');
            $table->unique('slug');
        });
    }
};
