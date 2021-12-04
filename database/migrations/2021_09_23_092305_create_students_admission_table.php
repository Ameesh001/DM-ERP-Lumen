<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsAdmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_admission', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('std_registration_id');
            $table->foreign('std_registration_id')->references('id')->on('student_registration');
            $table->string('registration_code', 40)->index('students_admission_registration_code_idx');
            $table->string('adm_code_prefix', 50)->nullable();
            $table->string('admission_code', 40)->index('students_admission_admission_code_idx');
            $table->integer('gr_no');
            $table->integer('session_id');
            $table->foreign('session_id')->references('id')->on('session');
            $table->date('admission_date');
            $table->string('admission_month');
            $table->date('joinning_date');
            $table->string('batch');
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');
            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
            $table->integer('section_id');
            $table->foreign('section_id')->references('id')->on('section');
            $table->string('student_name', 50);
            $table->string('father_name', 50);
            $table->string('gender', 10);
            $table->date('dob');
            $table->string('father_nic', 14)->nullable();           
            $table->string('mother_nic', 14)->nullable();
            $table->string('home_cell_no', 14);            
            $table->string('father_cell_no', 14);
            $table->string('mother_cell_no', 14)->nullable();            
            $table->text('home_address')->nullable();
            $table->string('place_of_birth', 25)->nullable();     
            $table->string('blood_group', 10)->nullable();     
            $table->string('religion', 15)->nullable();     
            $table->string('nationality', 20)->nullable();     
            $table->string('caste', 30)->nullable();     
            $table->string('community', 30)->nullable();     
            $table->string('is_physically_fit', 30)->nullable();     
            $table->string('school_last_attended', 50)->nullable();     
            $table->string('grade', 15)->nullable();     
            $table->string('native_language', 20)->nullable();     
            $table->string('other_language', 20)->nullable();     
            
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
        Schema::dropIfExists('student_admission');
    }
}
