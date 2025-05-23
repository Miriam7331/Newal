<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules_has_units', function (Blueprint $table) {
            $table->integer('modules_id')->index('fk_modules_has_units_modules1_idx');
            $table->integer('units_id')->index('fk_modules_has_units_units1_idx');
            $table->integer('id', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules_has_units');
    }
};
