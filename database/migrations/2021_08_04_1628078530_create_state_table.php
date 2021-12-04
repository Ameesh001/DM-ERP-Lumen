<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStateTable extends Migration
{
    public function up()
    {
        Schema::create('state', function (Blueprint $table) {

		$table->integer('id',true);
		$table->integer('countries_id');
		$table->string('state_name');
		$table->boolean('is_enable')->default(1);
		$table->timestamps();
                $table->integer('created_by')->nullable();
                $table->integer('updated_by')->nullable();
                $table->softDeletes();
		$table->foreign('countries_id')->references('id')->on('countries');
        });
    }

    public function down()
    {
        Schema::dropIfExists('state');
    }
}