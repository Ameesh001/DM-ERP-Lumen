<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeSlipDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fee_slip_detail', function (Blueprint $table) {
            $table->integer('id', true);
            
            $table->integer('fee_slip_id');
            $table->foreign('fee_slip_id')->references('id')->on('fee_slip_master');
            $table->string('fee_month', 20);
            $table->string('fee_month_code', 20);
            $table->date('fee_date');
            
            $table->integer('fee_type_id');
            $table->foreign('fee_type_id')->references('id')->on('fee_type');
            $table->integer('fee_amount');
            $table->integer('discount_percentage');
            $table->integer('discount_amount');
            
            
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
        Schema::dropIfExists('fee_slip_detail');
    }
}
