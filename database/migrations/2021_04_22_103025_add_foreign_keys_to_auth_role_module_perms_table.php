<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthRoleModulePermsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('auth_role_module_perms', function(Blueprint $table)
		{
			$table->foreign('role_id', 'auth_role_module_perms_ibfk_1')->references('id')->on('auth_roles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('module_id', 'auth_role_module_perms_ibfk_2')->references('id')->on('auth_modules')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('auth_role_module_perms', function(Blueprint $table)
		{
			$table->dropForeign('auth_role_module_perms_ibfk_1');
			$table->dropForeign('auth_role_module_perms_ibfk_2');
		});
	}

}
