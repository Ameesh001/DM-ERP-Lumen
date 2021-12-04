<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradingExamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grading_exam', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('grading_type_id');
            $table->foreign('grading_type_id')->references('id')->on('grading_type');
            $table->integer('grading_remarks_id');
            $table->foreign('grading_remarks_id')->references('id')->on('grading_remarks');
            $table->string('grade_name',50);
            $table->integer('percentage_from');
            $table->integer('percentage_end');
            $table->string('desc',50);
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
        Schema::dropIfExists('grading_exam');
    }
}
