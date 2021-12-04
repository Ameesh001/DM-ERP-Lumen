<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDesignationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('designation', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('department_id');
            $table->foreign('department_id')->references('id')->on('department');
            $table->string('desig_name', 100)->index('desig_name');
            $table->string('desig_desc', 100)->index('desig_desc');
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->integer('is_enable')->default(1)->index('is_enable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('designation');
    }
}
