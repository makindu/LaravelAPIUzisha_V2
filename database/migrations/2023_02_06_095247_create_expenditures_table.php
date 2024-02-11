<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpendituresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenditures', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('money_id')->nullable();
            $table->integer('ticket_office_id')->nullable();
            $table->double('amount');
            $table->string('motif')->nullable();
            $table->integer('account_id')->nullable();
            $table->boolean('is_validate')->nullable();
            $table->string('uuid')->nullable();
            $table->boolean('sync_status')->nullable();
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
        Schema::dropIfExists('expenditures');
    }
}
