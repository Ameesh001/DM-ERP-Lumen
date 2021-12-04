<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('countries', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('lang_id',50)->nullable();
			$table->string('lang_name',250)->nullable();
			$table->string('country_name', 100);
			$table->string('country_full_name', 150)->nullable();
			$table->string('dialing_code', 10);
			$table->string('short_code', 3);
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
		Schema::drop('countries');
	}

}
