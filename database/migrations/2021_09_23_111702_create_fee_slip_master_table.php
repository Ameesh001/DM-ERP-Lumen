<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeSlipMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fee_slip_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('admission_code', 40)->index('fee_slip_master_admission_code_idx');
            $table->integer('gr_no');
            $table->integer('session_id');
            $table->foreign('session_id')->references('id')->on('session');
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');
            $table->integer('class_id');
            $table->foreign('class_id')->references('id')->on('class');
            $table->integer('section_id');
            $table->foreign('section_id')->references('id')->on('section');
            $table->date('slip_issue_date');
            $table->date('slip_validity_date');
            $table->date('slip_due_date');
            $table->integer('slip_type_id');
            $table->foreign('slip_type_id')->references('id')->on('slip_type');
            $table->integer('kuickpay_id');
            $table->integer('bank_id');
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
        Schema::dropIfExists('fee_slip_master');
    }
}
