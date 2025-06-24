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
        Schema::table('users', function (Blueprint $table) {
            // Ajout de la colonne admin_principal_id pour lier les utilisateurs secondaires à leur admin principal
            // Elle est nullable car les admin_principal eux-mêmes n'auront pas de parent.
            $table->unsignedBigInteger('admin_principal_id')->nullable()->after('id');

            // Ajout de la colonne company_id pour lier les utilisateurs secondaires à une entreprise spécifique
            // Elle est nullable car les admin_principal gèrent plusieurs entreprises et ne sont pas liés à une seule via cette colonne.
            $table->unsignedBigInteger('company_id')->nullable()->after('admin_principal_id');

            // Ajout des clés étrangères
            $table->foreign('admin_principal_id')->references('id')->on('users')->onDelete('cascade');
            // onDelete('cascade') signifie que si un admin_principal est supprimé, ses utilisateurs secondaires le sont aussi.
            // À discuter si c'est le comportement souhaité. Alternative: onDelete('set null') si tu veux garder les utilisateurs mais les "dé-parenter".

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // onDelete('cascade') signifie que si une entreprise est supprimée, les utilisateurs qui lui sont rattachés le sont aussi.
            // C'est un comportement fort, à valider selon ta logique métier. Si tu veux plutôt empêcher la suppression de l'entreprise ou nullifier les IDs, adapte.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprime les clés étrangères avant de supprimer les colonnes
            $table->dropForeign(['admin_principal_id']);
            $table->dropForeign(['company_id']);

            // Supprime les colonnes ajoutées
            $table->dropColumn('admin_principal_id');
            $table->dropColumn('company_id');
        });
    }
};