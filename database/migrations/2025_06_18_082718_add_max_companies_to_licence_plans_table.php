<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licence_plans', function (Blueprint $table) {
            $table->integer('max_companies')->default(1)->after('price_xof')->comment('Nombre maximum d\'entreprises autorisées par cette licence. -1 pour illimité.');
        });
    }

    public function down(): void
    {
        Schema::table('licence_plans', function (Blueprint $table) {
            $table->dropColumn('max_companies');
        });
    }
};
