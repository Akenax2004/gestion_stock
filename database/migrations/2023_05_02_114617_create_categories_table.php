<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            // La colonne 'code' est commentée, donc non créée.
            //$table->string('code')->unique(); 
            
            // La clé étrangère 'user_id' est retirée.
            $table->foreignId('user_id')
                   ->constrained('users')
                   ->nullOnDelete();

            $table->timestamps(); // Ceci crée automatiquement les colonnes `created_at` et `updated_at`.
        });
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
