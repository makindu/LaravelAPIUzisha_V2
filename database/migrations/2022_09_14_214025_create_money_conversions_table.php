<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyConversionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money_conversions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('money_id1')->unsigned();
            $table->foreign('money_id1')->references('id')->on('moneys')->onDelete('cascade'); 
            $table->bigInteger('money_id2')->unsigned();
            $table->foreign('money_id2')->references('id')->on('moneys')->onDelete('cascade');  
            $table->double('rate');
            $table->string('operator')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
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
        Schema::dropIfExists('money_conversions');
    }
}
