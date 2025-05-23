<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('formative_actions_has_students', function (Blueprint $table) {
            $table->string('file')->nullable();
            $table->string('original_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formative_actions_has_students', function (Blueprint $table) {
            $table->dropColumn('file');
            $table->dropColumn('original_name');
        });
    }
};
