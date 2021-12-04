<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamMarksRegisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_marks_register', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('examination_id');
            $table->foreign('examination_id')->references('id')->on('examination');
            $table->integer('exam_setup_id');
            $table->foreign('exam_setup_id')->references('id')->on('exam_setup');
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('session_id');
            $table->foreign('session_id')->references('id')->on('session'); 

            $table->integer('assign_exam_subject_id');
            $table->foreign('assign_exam_subject_id')->references('id')->on('assign_exam_subject'); 

            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
            $table->integer('section_id');
            $table->foreign('section_id')->references('id')->on('section');


            $table->integer('grading_exam_id');
            $table->foreign('grading_exam_id')->references('id')->on('grading_exam');

            $table->integer('std_admission_id');
            $table->foreign('std_admission_id')->references('id')->on('student_admission');
            
            $table->integer('admission_code');
            $table->integer('gr_no');
            $table->integer('obtain_marks');
            $table->integer('percentage');
            $table->integer('exam_attendance');
            
            
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
        Schema::dropIfExists('exam_marks_register');
    }
}
