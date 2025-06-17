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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Customer::class)
                ->constrained();
            $table->string('order_date');
            $table->tinyInteger('order_status')
                ->comment('0 - Pending / 1 - Complete');
            $table->integer('total_products');
            $table->integer('sub_total');
            $table->integer('vat');
            $table->integer('total');
            $table->string('invoice_no');
            $table->string('payment_type');
            $table->integer('pay');
            $table->integer('due');
            // AJOUT DE LA LIGNE SUIVANTE
            $table->foreignId('user_id')->constrained('users')->nullOnDelete(); // Assure que les commandes sont liées à l'utilisateur qui les a créées/gérées
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};