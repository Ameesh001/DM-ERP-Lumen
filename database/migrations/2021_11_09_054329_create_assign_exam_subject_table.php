<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignExamSubjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_exam_subject', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('exam_setup_id');
            $table->foreign('exam_setup_id')->references('id')->on('exam_setup');
            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
            $table->integer('subject_id');
            $table->foreign('subject_id')->references('id')->on('subject');
            $table->integer('max_marks');
            $table->integer('passing_marks');
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
        Schema::dropIfExists('assign_exam_subject');
    }
}
