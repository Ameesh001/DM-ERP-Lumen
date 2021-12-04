<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWfMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wf_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('organization_id');
            $table->foreign('organization_id')->references('id')->on('organization_list');
            $table->integer('module_id');
            $table->foreign('module_id')->references('id')->on('auth_modules');
            $table->integer('doc_type_id');
            $table->foreign('doc_type_id')->references('id')->on('auth_modules');
            $table->string('wf_name', 50);
            $table->text('wf_desc');
            $table->date('wf_from_date')->nullable();
            $table->date('wf_end_date')->nullable();
            $table->tinyInteger('check_validity');
            $table->string('wf_start_on',50)->nullable();
            $table->integer('wf_level')->comments('Number of Approvals');
            $table->integer('wf_hierarchy_level');
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
        Schema::dropIfExists('wf_master');
    }
}
