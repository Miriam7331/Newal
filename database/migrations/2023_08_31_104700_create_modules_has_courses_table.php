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
        Schema::create('modules_has_courses', function (Blueprint $table) {
            $table->integer('modules_id')->index('fk_modules_has_courses_modules1_idx');
            $table->integer('courses_id')->index('fk_modules_has_courses_courses1_idx');
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
        Schema::dropIfExists('modules_has_courses');
    }
};
