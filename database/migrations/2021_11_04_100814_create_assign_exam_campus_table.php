<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignExamCampusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_exam_campus', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('assign_exam_hierarchy_id');
            $table->foreign('assign_exam_hierarchy_id')->references('id')->on('assign_exam_hierarchy');
            $table->integer('examination_id');
            $table->foreign('examination_id')->references('id')->on('examination');
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
        Schema::dropIfExists('assign_exam_campus');
    }
}
