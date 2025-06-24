<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('licence_plans', function (Blueprint $table) {
            // Vérifie si la colonne 'max_users' existe et la renomme si c'est le cas
            // Sinon, ajoute directement 'max_users_per_company'
            if (Schema::hasColumn('licence_plans', 'max_users')) {
                $table->renameColumn('max_users', 'max_users_per_company');
            } else {
                $table->integer('max_users_per_company')
                      ->default(0) // Valeur par défaut 0, peut être -1 pour illimité
                      ->comment('Nombre maximal d\'utilisateurs secondaires autorisés par entreprise pour cette licence. -1 pour illimité.')
                      ->after('max_companies'); // 
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licence_plans', function (Blueprint $table) {
            // Logique de retour arrière, dépend de si la colonne existait ou non
            if (Schema::hasColumn('licence_plans', 'max_users_per_company')) {
                // Si la colonne a été ajoutée, nous la supprimons
                if (!Schema::hasColumn('licence_plans', 'max_users')) { // Vérifie si 'max_users' n'existait pas avant, donc on peut supprimer
                    $table->dropColumn('max_users_per_company');
                } else {
                    // Si elle a été renommée, on la renomme de nouveau
                    $table->renameColumn('max_users_per_company', 'max_users');
                }
            }
        });
    }
};