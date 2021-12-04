<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city', function (Blueprint $table) {
            $table->integer('id',true);
            $table->string('city_name', 20)->index('city_name');
            $table->string('city_desc', 100);
            $table->integer('is_enable')->default(1);
            $table->timestamps();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('countries_id');
            $table->foreign('countries_id')->references('id')->on('countries');
            $table->integer('state_id');
            $table->foreign('state_id')->references('id')->on('state');
            $table->integer('region_id');
            $table->foreign('region_id')->references('id')->on('region');
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('city');
    }
}
