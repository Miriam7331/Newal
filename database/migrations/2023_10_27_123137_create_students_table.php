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
        Schema::create('students', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name');
            $table->string('email');
            $table->tinyInteger('level')->nullable();
            $table->string('dni')->nullable();
            $table->string('ssn')->nullable();
            $table->integer('phone');
            $table->string('address')->nullable();
            $table->string('cp')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('island')->nullable();
            $table->string('gender')->nullable();
            $table->date('birthdate')->nullable();
            $table->integer('disability')->default(0);
            $table->tinyInteger('consent')->default(0);
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
        Schema::dropIfExists('students');
    }
};
