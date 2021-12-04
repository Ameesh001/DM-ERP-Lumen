<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStdRegistrationInterviewTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('std_registration_interview_test', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('std_registration_id')->index('std_test_registration_id_idx');
            $table->foreign('std_registration_id')->references('id')->on('student_registration');
            $table->string('registration_code', 40)->index('std_test_registration_code_idx');
            $table->foreign('registration_code')->references('registration_code')->on('student_registration');
            $table->string('obtained_marks', 50);
            $table->integer('final_result_id');
            $table->foreign('final_result_id')->references('id')->on('std_final_results');
            $table->string('test_remarks', 200)->nullable();
            $table->integer('is_interview')->nullable();
            $table->string('interview_remarks', 200)->nullable();
            $table->date('interview_date')->nullable();
            $table->integer('is_nazra_test')->nullable();
            $table->string('nazra_current_lesson', 100)->nullable();
            $table->date('nazra_date')->nullable();
            $table->string('nazra_remarks', 100)->nullable();
            $table->integer('is_seat_alloted')->nullable();
            $table->timestamps();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('std_registration_interview_test');
    }
}
