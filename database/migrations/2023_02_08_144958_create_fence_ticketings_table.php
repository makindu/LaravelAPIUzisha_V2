<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFenceTicketingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fence_ticketings', function (Blueprint $table) {
            $table->id();
            $table->integer('fence_id');
            $table->integer('money_id');
            $table->integer('amount');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users'); 
            $table->string('ticketing')->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('fence_ticketings');
    }
}
