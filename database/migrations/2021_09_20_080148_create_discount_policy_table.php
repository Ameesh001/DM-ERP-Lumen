<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountPolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_policy', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('disc_code', 50);
            $table->integer('discount_type');
            $table->foreign('discount_type')->references('id')->on('discount_type');
            $table->integer('fees_type_id');
            $table->foreign('fees_type_id')->references('id')->on('fee_type');
            $table->string('disc_percentage', 50);
            $table->string('condition', 50);
            $table->string('discription', 100);
            $table->integer('disc_amount');
            $table->date('disc_from_date');
            $table->date('disc_end_date');
            $table->integer('disc_is_new_addmission')->comments('0=No, 1=Yes');
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
        Schema::dropIfExists('discount_policy');
    }
}
