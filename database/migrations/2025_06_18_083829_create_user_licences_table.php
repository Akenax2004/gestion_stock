<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_licences', function (Blueprint $table) { // Renommez la table si vous le souhaitez
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Lié à l'utilisateur
            $table->foreignId('licence_plan_id')->constrained('licence_plans')->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('TRIAL');
            $table->string('transaction_id')->nullable();
            $table->timestamps();

            $table->unique('user_id'); // Un seul plan de licence actif par utilisateur
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_licences');
    }
};