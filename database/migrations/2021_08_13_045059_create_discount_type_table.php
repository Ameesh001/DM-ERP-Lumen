<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_type', function (Blueprint $table) {
            $table->integer('id',true);
            $table->string('discount_type', 100)->index('discount_type');
            $table->string('discount_desc', 100)->index('discount_desc');
            $table->integer('is_enable')->default(1)->index('discount_type_is_enable');
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
        Schema::dropIfExists('discount_type');
    }
}
