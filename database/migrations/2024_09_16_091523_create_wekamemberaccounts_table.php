<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWekamemberaccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wekamemberaccounts', function (Blueprint $table) {
            $table->id(); 
            $table->double('sold')->nullable();
            $table->string('description');
            $table->string('type')->default('internal');
            $table->string('account_status')->default('disabled');
            $table->string('account_number')->nullable();
            $table->bigInteger('money_id')->unsigned();
            $table->foreign('money_id')->references('id')->on('moneys')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('wekamemberaccounts');
    }
}
