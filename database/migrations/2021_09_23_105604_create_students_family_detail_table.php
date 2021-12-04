<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsFamilyDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_family_detail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('std_admission_id');
            $table->foreign('std_admission_id')->references('id')->on('student_admission');
            $table->string('admission_code', 40)->index('students_family_detail_admission_code_idx');
            $table->string('father_qualification', 50);
            $table->string('father_company_name', 20);
            $table->string('father_company_address', 50);
            $table->string('father_office_cell', 15);
            $table->string('father_monthly_income', 20);
            $table->string('father_designation', 20);
            
            $table->string('mother_qualification', 50);
            $table->string('mother_company_name', 20);
            $table->string('mother_company_address', 50);
            
            $table->string('guardians_name', 20)->nullable();
            $table->string('guardians_phone_num', 15)->nullable();
            $table->string('guardians_company_name', 20)->nullable();
            $table->string('guardians_company_address', 50)->nullable();
            $table->string('guardians_designation', 20)->nullable();
            
            $table->string('others_siblings', 50)->nullable();
            $table->string('associated_dawateislami', 50);
            $table->string('no_of_years', 10)->nullable();
            $table->string('responsibility', 50)->nullable();
            
            
            
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
        Schema::dropIfExists('students_family_detail');
    }
}
