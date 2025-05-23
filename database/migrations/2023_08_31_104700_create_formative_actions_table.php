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
        Schema::create('formative_actions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code', 191);
            $table->integer('courses_id')->nullable();
            $table->integer('modules_id')->nullable();
            $table->integer('sectors_id');
            $table->integer('centers_id');
            $table->string('schedule', 191)->nullable();
            $table->string('islands', 191)->required();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('price')->nullable();
            $table->string('type', 191)->nullable();
            $table->integer('teachers_id')->nullable();
            $table->longText('requirements')->nullable();
            $table->string('receiver', 191)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formative_actions');
    }
};
