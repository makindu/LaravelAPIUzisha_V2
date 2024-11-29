<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvancesalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advancesalaries', function (Blueprint $table) {
            $table->id();
            $table->double('amount');
            $table->string('description')->nullable();
            $table->bigInteger('agent_id')->unsigned();
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');  
            $table->bigInteger('done_by_id')->unsigned();
            $table->foreign('done_by_id')->references('id')->on('users')->onDelete('cascade');  
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->bigInteger('money_id')->unsigned();
            $table->foreign('money_id')->references('id')->on('moneys');
            $table->date('done_at');
            $table->string('uuid');
            $table->string('status')->default("pending");
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
        Schema::dropIfExists('advancesalaries');
    }
}
