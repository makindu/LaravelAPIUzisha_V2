<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fences', function (Blueprint $table) {
            $table->id();
            $table->double('amount_due');
            $table->double('amount_paid')->nullable();
            $table->double('totalsell')->nullable();
            $table->double('totalcash')->nullable();
            $table->double('totalcredits')->nullable();
            $table->double('totalbonus')->nullable();
            $table->double('totalcautions')->nullable();
            $table->double('totaldebts')->nullable();
            $table->double('depositcautions')->nullable();
            $table->double('totalexpenditures')->nullable();
            $table->double('totalentries')->nullable();
            $table->double('sold')->nullable();
            $table->integer('money_id')->nullable();
            $table->string('uuid')->nullable();
            $table->boolean('sync_status')->nullable();
            $table->boolean('validated')->nullable();
            $table->date('date_concerned');
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
        Schema::dropIfExists('fences');
    }
}
