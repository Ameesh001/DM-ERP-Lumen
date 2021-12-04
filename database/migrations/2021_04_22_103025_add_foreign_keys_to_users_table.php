<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->foreign('user_type', 'users_user_type_fk')->references('id')->on('auth_user_types')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('role_id', 'users_role_id_fk')->references('id')->on('auth_roles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropForeign('users_user_type_fk');
			$table->dropForeign('users_role_id_fk');
		});
	}

}
