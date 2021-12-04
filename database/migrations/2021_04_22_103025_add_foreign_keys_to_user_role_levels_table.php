<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUserRoleLevelsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_role_levels', function(Blueprint $table)
		{
			$table->foreign('user_id', 'user_role_levels_ibfk_1')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('role_id', 'user_role_levels_ibfk_2')->references('id')->on('auth_roles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_role_levels', function(Blueprint $table)
		{
			$table->dropForeign('user_role_levels_ibfk_1');
			$table->dropForeign('user_role_levels_ibfk_2');
		});
	}

}
