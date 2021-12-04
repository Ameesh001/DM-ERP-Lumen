<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeStructureDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fee_structure_detail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('fees_code', 50);
            $table->integer('fees_master_id');
            $table->foreign('fees_master_id')->references('id')->on('fee_structure_master');
            $table->integer('fees_type_id');
            $table->foreign('fees_type_id')->references('id')->on('fee_type');
            $table->integer('fees_amount');
            $table->date('fees_from_date');
            $table->date('fees_end_date');
            $table->integer('fees_is_new_addmission')->comments('0=No, 1=Yes');
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
        Schema::dropIfExists('fee_structure_detail');
    }
}
