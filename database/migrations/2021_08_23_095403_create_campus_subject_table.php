<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampusSubjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campus_subject', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');
            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
            $table->integer('subject_id');
            $table->foreign('subject_id')->references('id')->on('subject');
            $table->integer('class_duration');
            $table->integer('online_class_duration');
            $table->integer('subject_marks');
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
        Schema::dropIfExists('campus_subject');
    }
}
