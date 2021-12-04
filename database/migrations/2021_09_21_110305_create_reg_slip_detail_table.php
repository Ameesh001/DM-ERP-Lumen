<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegSlipDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reg_slip_detail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('reg_slip_master_id');
            $table->foreign('reg_slip_master_id')->references('id')->on('reg_slip_master');
            $table->integer('slip_type_id');
            $table->foreign('slip_type_id')->references('id')->on('slip_type');
            $table->integer('fees_type_id');
            $table->foreign('fees_type_id')->references('id')->on('fee_type');
            $table->string('month', 50);
            $table->integer('fee_charges')->default(0);
            $table->integer('discount_amount')->default(0);
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
        Schema::dropIfExists('reg_slip_detail');
    }
}
