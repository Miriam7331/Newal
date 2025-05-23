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
        Schema::create('students_has_modules', function (Blueprint $table) {
            $table->integer('students_id')->index('fk_students_has_modules_students1_idx');
            $table->integer('modules_id')->index('fk_students_has_modules_modules1_idx');
            $table->integer('id', true);
            $table->string('status')->default('Apto');
            $table->integer('formative_actions_id')->index('fk_students_has_modules_formative_actions1_idx');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students_has_modules');
    }
};
