<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->integer('service_id');
            $table->integer('invoice_id');
            $table->integer('pos_id')->nullable();
            $table->integer('deposit_id');
            $table->double('quantity');
            $table->double('price');
            $table->integer('money_id')->nullable();
            $table->double('total')->nullable();
            // $table->string('description')->nullable();
            $table->string('uuid')->nullable();
            $table->boolean('sync_status')->nullable();
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
        Schema::dropIfExists('invoice_details');
    }
}
