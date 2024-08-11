<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('edited_by_id');
            $table->integer('customer_id')->nullable();
            $table->double('total');
            $table->double('back')->nullable();
            $table->double('total_ht')->nullable();
            $table->double('totalespeces')->nullable();
            $table->double('totalcreditcard')->nullable();
            $table->double('totalmobilemoney')->nullable();
            $table->integer('money_id')->nullable();
            $table->string('type_facture');
            $table->double('amount_paid')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('ref_payment')->nullable();
        	$table->double('discount')->nullable();
            $table->double('vat_amount')->nullable();
            $table->double('vat_percent')->nullable();
            $table->boolean('is_validate_discount')->nullable();
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->string('note')->nullable();
            $table->integer('servant_id')->nullable();
            $table->integer('table_id')->nullable();
            $table->string('sync_status')->nullable();
            $table->string('status')->nullable();
            $table->string('uuid')->nullable();
            $table->double('total_received')->nullable();
            $table->double('netToPay')->nullable();
            $table->dateTimeTz('done_at')->nullable();
            $table->date('date_operation')->nullable();
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
        Schema::dropIfExists('invoices');
    }
}
