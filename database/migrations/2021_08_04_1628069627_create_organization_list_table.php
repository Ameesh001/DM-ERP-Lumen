<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrganizationListTable extends Migration
{
    public function up()
    {
        Schema::create('organization_list', function (Blueprint $table) {

		$table->integer('id', true);
		$table->string('org_prefix',5);
		$table->string('org_name',20);
		$table->string('org_logo',50);
		$table->integer('countries_id')->nullable();
		$table->text('org_address')->nullable();
		$table->string('org_contact',20)->nullable();
		$table->string('affiliation_board_id',50)->nullable();
		$table->boolean('is_enable')->default(1)->index('org_is_enable_idx');
		$table->timestamps();
                $table->integer('created_by')->nullable();
                $table->integer('updated_by')->nullable();
                $table->softDeletes();
		$table->foreign('countries_id')->references('id')->on('countries')->index('org_countries_id_fk');
 
        });
    }

    public function down()
    {
        Schema::dropIfExists('organization_list');
    }
}