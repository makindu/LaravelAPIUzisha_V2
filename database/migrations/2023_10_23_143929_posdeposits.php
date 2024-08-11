<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Posdeposits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posdeposits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('deposit_id')->unsigned();
            $table->foreign('deposit_id')->references('id')->on('deposit_controllers'); 
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('pos_id')->unsigned();
            $table->foreign('pos_id')->references('id')->on('point_of_sales');
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
        //
    }
}
