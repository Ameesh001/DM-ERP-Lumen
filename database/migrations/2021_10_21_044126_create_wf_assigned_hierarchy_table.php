<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWfAssignedHierarchyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wf_assigned_hierarchy', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('wf_id');
            $table->foreign('wf_id')->references('id')->on('wf_master');
            $table->integer('countries_id');
            $table->foreign('countries_id')->references('id')->on('countries');
            $table->integer('state_id')->nullable();
            $table->foreign('state_id')->references('id')->on('state');
            $table->integer('region_id')->nullable();
            $table->foreign('region_id')->references('id')->on('region');
            $table->integer('city_id')->nullable();
            $table->foreign('city_id')->references('id')->on('city');
            $table->integer('campus_id')->nullable();
            $table->foreign('campus_id')->references('id')->on('campus');
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
        Schema::dropIfExists('wf_assigned_hierarchy');
    }
}
