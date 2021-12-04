<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWfDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wf_detail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('wf_id');
            $table->foreign('wf_id')->references('id')->on('wf_master');
            $table->integer('wf_level');
            $table->integer('assigned_role_id');
            $table->foreign('assigned_role_id')->references('id')->on('auth_roles');
            $table->integer('amount');
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->integer('is_enable')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wf_detail');
    }
}
