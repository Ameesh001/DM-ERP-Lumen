<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentRegistrationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_registration', function (Blueprint $table) {

            $table->integer('id', true);
            $table->integer('nationality_id');
            $table->foreign('nationality_id')->references('id')->on('countries');
            $table->integer('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');
            $table->integer('session_id');
            $table->foreign('session_id')->references('id')->on('session'); 
            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
            $table->integer('admission_type_id');
            $table->foreign('admission_type_id')->references('id')->on('admission_type');

            $table->string('reg_code_prefix', 50);
            $table->string('registration_code', 40)->index('student_registration_registration_code');
            $table->date('registration_date')->index('student_registration_registration_date');
            $table->string('first_name', 50);
            $table->string('last_name', 50)->nullable();
            $table->string('full_name', 100);
            $table->string('address', 200);
            $table->date('dob');
            $table->string('religion', 50);
            $table->string('father_name', 50);
            $table->string('father_nic', 14);
            $table->string('email', 50)->nullable();
            $table->string('phone_no', 30);
            $table->string('father_cell_no', 30);
            $table->string('mother_cell_no', 30)->nullable();
            $table->string('father_occupation', 50);
            $table->integer('Father_salary')->nullable();
            $table->string('prev_school', 100)->nullable();
            $table->string('reason_for_leaving', 200)->nullable();
            $table->double('student_age');
            $table->string('gender', 10);
            $table->integer('is_required_test');
            $table->date('test_date')->nullable();
            $table->string('test_time', 20)->nullable();
            $table->text('student_img')->nullable();
            $table->string('comments', 50)->nullable();
            $table->string('remarks', 100)->nullable();
            
            $table->integer('entry_status')->comment('1: normal , 2: online')->default(1);
            
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
        Schema::dropIfExists('student_registration');
    }
}
