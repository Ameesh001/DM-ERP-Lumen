<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthRoleModulePermsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('auth_role_module_perms', function(Blueprint $table)
		{
			$table->integer('role_id')->index('role_id');
			$table->integer('module_id')->index('module_id');
			$table->string('route', 100)->nullable();
			$table->string('action', 100)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('auth_role_module_perms');
	}

}
