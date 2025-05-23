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
        Schema::create('companies', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('cif');
            $table->string('name');
            $table->string('email')->nullable();
            $table->integer('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('number')->nullable();
            $table->string('floor')->nullable();
            $table->string('door')->nullable();
            $table->string('municipality')->nullable();
            $table->string('cp');
            $table->string('island');
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
        Schema::dropIfExists('companies');
    }
};
