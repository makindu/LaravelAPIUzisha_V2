<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWekafirstentriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wekafirstentries', function (Blueprint $table) {
            $table->id();
            $table->double('amount');
            $table->string('description')->nullable();
            $table->bigInteger('done_by_id')->unsigned();
            $table->foreign('done_by_id')->references('id')->on('users')->onDelete('cascade');  
            $table->bigInteger('member_id')->unsigned();
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('collector_id')->unsigned();
            $table->foreign('collector_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->bigInteger('money_id')->unsigned();
            $table->foreign('money_id')->references('id')->on('moneys');
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->date('done_at');
            $table->integer('sync_status')->default(1);
            $table->string('uuid')->nullable()->unique();
            $table->boolean('cashed')->default(false);
            $table->integer('cashed_by')->nullable();
            $table->date('cashed_at')->nullable();
            $table->integer('fund')->nullable();
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
        Schema::dropIfExists('wekafirstentries');
    }
}
