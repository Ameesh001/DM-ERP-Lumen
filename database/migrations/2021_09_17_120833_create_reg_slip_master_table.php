<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegSlipMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reg_slip_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('fees_master_id');
            $table->foreign('fees_master_id')->references('id')->on('fee_structure_master');
            $table->string('challan_no',20);
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');
            $table->integer('std_registration_id')->index('std_registration_slip_id_idx');
            $table->foreign('std_registration_id')->references('id')->on('student_registration');
            $table->integer('slip_month_code');
            $table->string('slip_month_name', 20);
            $table->string('slip_month');
            $table->date('slip_date');
            $table->string('slip_fee_month');
            $table->integer('slip_payable_amount');
            $table->integer('slip_fine');
            $table->date('slip_issue_date');
            $table->date('slip_valid_date');
            $table->date('slip_due_date');
            $table->text('slip_remarks');
            $table->integer('slip_status')->default(1)->comments('1=Unpaid 2=Paid, 3=Cancel');
            $table->date('rec_date');
            $table->integer('kuickpay_id');
            $table->integer('bank_id');
            $table->integer('has_discount');

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
        Schema::dropIfExists('reg_slip_master');
    }
}
