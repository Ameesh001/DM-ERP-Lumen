<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('region', function (Blueprint $table) {
            $table->integer('id',true);
            $table->string('region_name', 20)->index('region_name');
            $table->string('region_desc', 100);
            $table->integer('is_enable')->default(1);
            $table->timestamps();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('countries_id');
            $table->foreign('countries_id')->references('id')->on('countries');
            $table->integer('state_id');
            $table->foreign('state_id')->references('id')->on('state');
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
        Schema::dropIfExists('region');
    }
}
