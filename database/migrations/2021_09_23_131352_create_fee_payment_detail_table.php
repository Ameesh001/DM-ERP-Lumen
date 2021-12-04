<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeePaymentDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fee_payment_detail', function (Blueprint $table) {
            $table->integer('id', true);
            
            $table->integer('fee_slip_detail_id');
            $table->foreign('fee_slip_detail_id')->references('id')->on('fee_slip_detail');
            
            $table->string('fee_month', 20);
            $table->date('fee_date');
            $table->date('payment_date');
            $table->integer('total_fee_amount');
            $table->integer('total_discount_amount');
            $table->integer('payable_amount');
            $table->integer('student_paid_amount');
            $table->integer('payment_status')->default(2)->comment('1-paid, 2-unpaid');
            
            $table->integer('payment_channel_id')->comment('entry should be Banks or online payment method reference');
            
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
        Schema::dropIfExists('fee_payment_detail');
    }
}
