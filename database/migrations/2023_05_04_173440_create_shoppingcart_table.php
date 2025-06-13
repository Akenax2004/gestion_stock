<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShoppingcartTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('cart.database.table'), function (Blueprint $table) {
<<<<<<< HEAD
            $table->string('identifier', 120);
            $table->string('instance', 120);
=======
            $table->string('identifier', 100); // Limité à 100 caractères
            $table->string('instance', 100);   // Limité à 100 caractères
>>>>>>> 58826724be3ba47de5bcd3dd0e3d1def8e583f42
            $table->longText('content');
            $table->nullableTimestamps();

            $table->primary(['identifier', 'instance']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('cart.database.table'));
    }
}
