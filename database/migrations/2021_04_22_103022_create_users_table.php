<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('keycloak_id', 255)->uniqid();
			$table->integer('role_id')->index('role_id_idx');
                        $table->string('username', 100);
			$table->string('firstName',100);
			$table->string('lastName', 100);
			$table->string('full_name', 100)->index('full_name');
			$table->string('password', 100)->nullable();
			$table->string('phone', 15);
			$table->string('email', 100)->nullable();
			$table->text('address', 65535)->nullable();
			$table->integer('user_type')->index('user_type');
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
		Schema::drop('users');
	}

}
