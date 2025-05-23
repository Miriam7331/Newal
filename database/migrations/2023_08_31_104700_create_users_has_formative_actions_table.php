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
        Schema::create('users_has_formative_actions', function (Blueprint $table) {
            $table->integer('users_id')->index('fk_users_has_formative_actions_users1_idx');
            $table->integer('formative_actions_id')->index('fk_users_has_formative_actions_formative_actions1_idx');
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
        Schema::dropIfExists('users_has_formative_actions');
    }
};
