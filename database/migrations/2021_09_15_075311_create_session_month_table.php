<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionMonthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_month', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('session_id');
            $table->foreign('session_id')->references('id')->on('session'); 
            $table->integer('month_no');
            $table->string('month_name', 15);
            $table->string('month_full_name',20);
            $table->integer('year_no');
            $table->integer('month_index');
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
        Schema::dropIfExists('session_month');
    }
}
