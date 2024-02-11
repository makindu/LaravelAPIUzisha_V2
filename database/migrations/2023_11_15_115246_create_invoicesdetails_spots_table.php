<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesdetailsSpotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoicesdetails_spots', function (Blueprint $table) {
            $table->id();
            $table->double('quantity');
            $table->bigInteger('detail_id')->unsigned();
            $table->foreign('detail_id')->references('id')->on('invoice_details'); 
            $table->bigInteger('spot_id')->unsigned();
            $table->foreign('spot_id')->references('id')->on('spots');
            $table->string('observation');
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
        Schema::dropIfExists('invoicesdetails_spots');
    }
}
