<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlipSetupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slip_setup', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('slip_type_id');
            $table->foreign('slip_type_id')->references('id')->on('slip_type');
            $table->date('month_close_date');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('validity_date');
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
        Schema::dropIfExists('slip_setup');
    }
}
