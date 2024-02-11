<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('service_id');
            $table->string('label')->nullable();
            $table->double('price');
            $table->integer('money_id')->nullable();
            $table->integer('principal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prices_categories');
    }
}
