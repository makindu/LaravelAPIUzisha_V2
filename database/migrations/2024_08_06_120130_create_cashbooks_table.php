<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashbooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashbooks', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('money_id')->nullable();
            $table->integer('ticket_office_id')->nullable();
            $table->double('amount');
            $table->string('motif')->nullable();
            $table->integer('account_id')->nullable();
            $table->boolean('validate')->nullable();
            $table->string('uuid')->nullable();
            $table->string('type')->nullable();
            $table->boolean('sync_status')->nullable();
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->date('done_at')->nullable();
            $table->integer('beneficiary')->nullable();
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
        Schema::dropIfExists('cashbooks');
    }
}
