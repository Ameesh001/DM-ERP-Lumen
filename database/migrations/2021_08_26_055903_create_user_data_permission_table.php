<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDataPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_data_permission', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('hierarchy_level_id');
            $table->foreign('hierarchy_level_id')->references('id')->on('user_hierarchy_levels');
            $table->integer('data_permissions_id');
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
        Schema::dropIfExists('user_data_permission');
    }
}
