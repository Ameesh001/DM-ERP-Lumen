<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('auth_roles', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('role_name', 100);
			$table->integer('organization_id')->index('roles_organization_id_idx');
			$table->foreign('organization_id')->references('id')->on('organization_list');
			$table->boolean('is_enable')->default(1)->index('is_enable');
			$table->timestamps();
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('auth_roles');
	}

}
