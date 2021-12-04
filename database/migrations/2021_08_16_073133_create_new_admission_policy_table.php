<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewAdmissionPolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_admission_policy', function (Blueprint $table) {
            $table->integer('id',true);
            $table->integer('countries_id');
            $table->integer('state_id');
            $table->integer('region_id');
            $table->integer('city_id');
            $table->integer('campus_id');
            $table->integer('class_id');
            $table->integer('min_year');
            $table->integer('min_month');
            $table->integer('max_year');
            $table->integer('max_month');
            $table->integer('is_enable')->default(1)->index('new_admission_policy_is_enable');
            $table->timestamps();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->foreign('countries_id')->references('id')->on('countries');
            $table->foreign('state_id')->references('id')->on('state');
            $table->foreign('class_id')->references('id')->on('class');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('new_admission_policy');
    }
}
