<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamSetupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_setup', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('examination_id');
            $table->foreign('examination_id')->references('id')->on('examination');
            $table->integer('assign_exam_campus_id');
            $table->foreign('assign_exam_campus_id')->references('id')->on('assign_exam_campus');
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
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
        Schema::dropIfExists('exam_setup');
    }
}
