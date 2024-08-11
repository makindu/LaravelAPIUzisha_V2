<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestapprovmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requestapprovments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('deposit_sender_id')->unsigned();
            $table->foreign('deposit_sender_id')->references('id')->on('deposit_controllers'); 
            $table->bigInteger('deposit_receiver_id')->unsigned()->nullable();
            $table->foreign('deposit_receiver_id')->references('id')->on('deposit_controllers');
            $table->double('quantity_sent');
            $table->double('quantity_received')->nullable();
            $table->string('note')->nullable();
            $table->string('comment')->nullable();
            $table->string('reference')->unique()->nullable();
            $table->bigInteger('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services_controllers');
            $table->bigInteger('sender_id')->unsigned();
            $table->foreign('sender_id')->references('id')->on('users');
            
            $table->bigInteger('receiver_id')->unsigned()->nullable();
            $table->foreign('receiver_id')->references('id')->on('users');

            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises');
            $table->string('status')->nullable();
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
        Schema::dropIfExists('requestapprovments');
    }
}
