<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher_time_table', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');
            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
            $table->integer('subject_id');
            $table->foreign('subject_id')->references('id')->on('subject');  
            $table->integer('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users');
            $table->string('day', 50);
            $table->string('class_start_time', 50);
            $table->string('class_end_time', 50);
            $table->string('break_start_time', 50)->nullable();
            $table->string('break_end_time', 50)->nullable();
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
        Schema::dropIfExists('teacher_time');
    }
}
