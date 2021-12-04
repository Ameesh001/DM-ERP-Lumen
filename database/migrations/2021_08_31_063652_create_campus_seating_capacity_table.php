<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampusSeatingCapacityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campus_seating_capacity', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('countries_id');
            $table->foreign('countries_id')->references('id')->on('countries');
            $table->integer('state_id');
            $table->foreign('state_id')->references('id')->on('state');
            $table->integer('region_id');
            $table->foreign('region_id')->references('id')->on('region');
            $table->integer('city_id');
            $table->foreign('city_id')->references('id')->on('city');
            $table->integer('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');
            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
            $table->integer('session_id');
            $table->foreign('session_id')->references('id')->on('session');
            $table->integer('section_id');
            $table->foreign('section_id')->references('id')->on('section');
            $table->string('gender',10)->index('gender');
            $table->integer('old_enrolled_no')->nullable();
            $table->integer('new_student_no')->nullable();
            $table->integer('dimension_capacity')->nullable();
            $table->integer('reserved_capacity')->nullable();
            $table->integer('fixed_capacity');
            $table->integer('room_no')->nullable();
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
        Schema::dropIfExists('campus_seating_capacity');
    }
}
