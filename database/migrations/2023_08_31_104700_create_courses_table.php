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
        Schema::create('courses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name')->unique();
            $table->longText('description')->nullable();
            $table->integer('level')->nullable();
            $table->string('code')->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('professional_families_id')->index('fk_courses_professional_families1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
