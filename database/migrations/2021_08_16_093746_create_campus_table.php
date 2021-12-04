<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campus', function (Blueprint $table) {
            $table->id();
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
            $table->string('campus_name', 100)->index('campus_name');
            $table->string('campus_address', 100);
            $table->string('campus_email', 50);
            $table->string('campus_cell', 20);
            $table->string('principle_name', 20);
            $table->string('principle_cell', 20);
            $table->string('principle_email', 50);
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
        Schema::dropIfExists('campus');
    }
}
