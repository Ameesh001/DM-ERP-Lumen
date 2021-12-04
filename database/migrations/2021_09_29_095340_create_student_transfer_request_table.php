<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentTransferRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_transfer_request', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');
            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
            $table->integer('section_id');
            $table->foreign('section_id')->references('id')->on('section');
            $table->integer('std_admission_id');
            $table->foreign('std_admission_id')->references('id')->on('student_admission');

            $table->integer('to_campus_id');
            $table->integer('to_class_id');
            $table->string('request_status', 100)->default('Inprogress');

            $table->integer('request_assigned_to');
            $table->foreign('request_assigned_to')->references('id')->on('users');


            $table->string('progress',50);
            $table->string('conduct',50);
            $table->string('reason_for_transfer',100);
            $table->string('tc_issue',5)->default('No');
            $table->text('remarks');

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
        Schema::dropIfExists('student_transfer_request');
    }
}
