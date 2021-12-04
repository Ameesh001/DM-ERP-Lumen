<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentSetupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_setup', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('assessment_category_id');
            $table->foreign('assessment_category_id')->references('id')->on('assessment_category');
            $table->integer('assessment_type_id');
            $table->foreign('assessment_type_id')->references('id')->on('assessment_type');
            $table->integer('assessment_master_id');
            $table->foreign('assessment_master_id')->references('id')->on('assessment_master');
            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
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
        Schema::dropIfExists('assessment_setup');
    }
}
