<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthLoginAttemptsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('auth_login_attempts', function(Blueprint $table)
		{
			$table->string('login_ip', 15);
			$table->string('pc_name');
			$table->string('user_name', 25);
			$table->date('date');
			$table->time('time');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('auth_login_attempts');
	}

}
