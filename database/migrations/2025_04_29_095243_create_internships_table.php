<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *@return void
     */
    public function up()
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('schedule', 191)->nullable();
            $table->integer('formative_actions_has_students_id');
            $table->integer('companies_id')->nullable();
            $table->timestamps();
            $table->unique(['formative_actions_has_students_id', 'companies_id'], 'internships_student_company_unique');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internships');
    }
};
