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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();

            $table->date('date');
            $table->string('reference');

            $table->foreignIdFor(\App\Models\Customer::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('customer_name');
            $table->integer('tax_percentage')->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('discount_percentage')->default(0);
            $table->integer('discount_amount')->default(0);
            $table->integer('shipping_amount')->default(0);
            $table->integer('total_amount');
            $table->tinyInteger('status');
            $table->text('note')->nullable();

            // AJOUT DE LA LIGNE SUIVANTE pour lier le devis à l'utilisateur
            $table->foreignId('user_id')
                ->constrained('users') // Assurez-vous que c'est bien la table 'users' de Laravel
                ->nullOnDelete(); // ou nullOnDelete() si un devis peut exister sans utilisateur créateur

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};