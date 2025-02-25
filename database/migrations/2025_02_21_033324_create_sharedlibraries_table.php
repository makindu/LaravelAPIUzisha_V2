<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharedlibrariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sharedlibraries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('library')->unsigned();
            $table->foreign('library')->references('id')->on('libraries')->onDelete('cascade');
            $table->bigInteger('sharedby')->unsigned();
            $table->foreign('sharedby')->references('id')->on('users')->onDelete('cascade'); 
            $table->bigInteger('sharedto')->unsigned();
            $table->foreign('sharedto')->references('id')->on('users')->onDelete('cascade');
            $table->string('message')->nullable();
            $table->string('status')->default('unread');
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
        Schema::dropIfExists('sharedlibraries');
    }
}
