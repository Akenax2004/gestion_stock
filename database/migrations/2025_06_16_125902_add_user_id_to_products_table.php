<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     * Cette méthode est appelée lorsque vous exécutez 'php artisan migrate'.
     * Elle ajoute la colonne 'user_id' à la table 'products'.
     */
    public function up(): void
    {
        // Utilise Schema::table pour modifier une table existante
        Schema::table('products', function (Blueprint $table) {
            // Ajoute la colonne 'user_id' qui est une clé étrangère vers la table 'users'.
            // 'constrained('users')' indique que cette colonne fait référence à la colonne 'id' de la table 'users'.
            // 'cascadeOnDelete()' signifie que si un utilisateur est supprimé, tous les produits associés à cet utilisateur
            // seront également supprimés. Vous pouvez changer ceci en 'nullOnDelete()' si vous préférez
            // que le 'user_id' devienne NULL si l'utilisateur est supprimé, plutôt que de supprimer les produits.
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Annule les migrations.
     * Cette méthode est appelée lorsque vous exécutez 'php artisan migrate:rollback'.
     * Elle supprime la colonne 'user_id' de la table 'products'.
     */
    public function down(): void
    {
        // Utilise Schema::table pour modifier une table existante
        Schema::table('products', function (Blueprint $table) {
            // D'abord, supprime la contrainte de clé étrangère pour éviter les erreurs.
            // Laravel génère un nom de contrainte par défaut que dropForeign peut cibler avec un tableau de colonnes.
            $table->dropForeign(['user_id']);
            // Ensuite, supprime la colonne 'user_id'.
            $table->dropColumn('user_id');
        });
    }
};
