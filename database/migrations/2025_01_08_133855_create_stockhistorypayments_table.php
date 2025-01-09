<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockhistorypaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stockhistorypayments', function (Blueprint $table) {
            $table->id();
            $table->double('amount');
            $table->string('note')->nullable();
            $table->bigInteger('stock_history_id')->unsigned();
            $table->foreign('stock_history_id')->references('id')->on('stock_history_controllers')->onDelete('cascade');
            $table->bigInteger('done_by')->unsigned();
            $table->foreign('done_by')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services_controllers')->onDelete('cascade'); 
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
        Schema::dropIfExists('stockhistorypayments');
    }
}
