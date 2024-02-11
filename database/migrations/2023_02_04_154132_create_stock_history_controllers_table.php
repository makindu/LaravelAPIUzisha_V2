<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockHistoryControllersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_history_controllers', function (Blueprint $table) {
            $table->id();
            $table->integer('depot_id')->nullable();	
            $table->integer('service_id');
            $table->integer('user_id');
            $table->integer('provider_id')->nullable();
            $table->integer('invoice_id')->nullable();
            $table->double('quantity');
            $table->double('quantity_before')->nullable();
            $table->double('price')->nullable();
            $table->double('total')->nullable();
            $table->double('price_used')->nullable();
            $table->double('quantity_used')->nullable();
            $table->double('profit')->nullable();
            $table->integer('operation_used')->nullable();
            $table->date('expiration_date')->nullable();
            $table->date('date_operation')->nullable();
            $table->integer('document_type')->nullable();
            $table->string('document_name')->nullable();
            $table->string('palette')->nullable();
            $table->string('method_used')->nullable();
            $table->string('document_number')->nullable();
            $table->string('attachment')->nullable();
            $table->string('motif')->nullable();
            $table->string('code_bar')->nullable();
            $table->string('note')->nullable();
            $table->string('type');
            $table->string('type_approvement');
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
        Schema::dropIfExists('stock_history_controllers');
    }
}
