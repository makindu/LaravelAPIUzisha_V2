<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePressingStockStoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pressing_stock_stories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('deposit_id')->unsigned();
            $table->foreign('deposit_id')->references('id')->on('deposit_controllers');
            $table->bigInteger('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services_controllers');
            $table->bigInteger('done_by')->unsigned();
            $table->foreign('done_by')->references('id')->on('users'); 
            $table->bigInteger('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customer_controllers');
            $table->bigInteger('invoice_id')->unsigned();
            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->bigInteger('detail_invoice_id')->unsigned();
            $table->foreign('detail_invoice_id')->references('id')->on('invoice_details');
            $table->double('quantity');
            $table->double('price')->nullable();
            $table->double('total')->nullable();
            $table->double('sold')->nullable();
            $table->string('note')->nullable();
            $table->string('type');
            $table->string('status')->nullable();
            $table->string('uuid')->nullable();
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->dateTimeTz('done_at')->nullable();
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
        Schema::dropIfExists('pressing_stock_stories');
    }
}
