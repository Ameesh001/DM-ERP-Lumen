<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('assessment_type_id');
            $table->foreign('assessment_type_id')->references('id')->on('assessment_type');
            $table->string('title',100)->collation('utf8_general_ci');
            $table->string('assessment_remarks',100)->collation('utf8_general_ci');
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
        Schema::dropIfExists('assessment_master');
    }
}
