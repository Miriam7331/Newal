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
        Schema::create('formative_actions_has_students', function (Blueprint $table) {
            $table->integer('formative_actions_id')->index('fk_formative_actions_has_students_formative_actions1_idx');
            $table->integer('students_id')->index('fk_formative_actions_has_students_students1_idx');
            $table->string('status')->default('Inscrito');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('formative_actions_has_students');
    }
};
