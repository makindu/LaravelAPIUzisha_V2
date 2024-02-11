<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersExpendituresLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_expenditures_limits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('limit_id')->unsigned();
            $table->foreign('limit_id')->references('id')->on('expenditures_limits');    
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users'); 
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
        Schema::dropIfExists('users_expenditures_limits');
    }
}
