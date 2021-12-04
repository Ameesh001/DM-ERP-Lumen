<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthModulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('auth_modules', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 100)->nullable();
			$table->string('default_url', 100);
			$table->string('module_name', 100)->comment('Controller Name')->nullable();
			$table->string('icon_class', 50)->nullable()->comment('Display icon');
			$table->integer('parent_id')->default(0)->index('auth_modules_ibfk_1')->comment('N level');
			$table->boolean('have_child')->default(0)->comment('0- No , 1- Yes');
			$table->json('allowed_permissions')->comment('JSON')->nullable();
			$table->integer('sorting');
			$table->integer('is_visible')->nullable()->default(1)->comment('1- Visible, 2- Disable');
			$table->integer('detail')->default(0)->comment('1- Parent, 2- Detail');
			$table->boolean('is_enable')->nullable()->index('is_enable');
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
		Schema::drop('auth_modules');
	}

}
