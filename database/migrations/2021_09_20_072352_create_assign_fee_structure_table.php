<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignFeeStructureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_fee_structure', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('fees_code', 50);
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->integer('state_id');
            $table->foreign('state_id')->references('id')->on('state');
            $table->integer('region_id');
            // $table->foreign('region_id')->references('id')->on('region');
            $table->integer('city_id');
            $table->foreign('city_id')->references('id')->on('city');
            $table->integer('campus_id');
            // $table->foreign('campus_id')->references('id')->on('campus');
            $table->integer('class_id');
            // $table->foreign('class_id')->references('id')->on('class');
            $table->string('admission_code', 20);
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
        Schema::dropIfExists('assign_fee_structure');
    }
}
