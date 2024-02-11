<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_offices', function (Blueprint $table) {
            $table->id();
            $table->integer('pos_id')->nullable();
            $table->integer('created_by_id')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('user_id')->nullable();
            $table->double('available_amount')->nullable();
            $table->string('sale_type')->nullable();
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
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
        Schema::dropIfExists('ticket_offices');
    }
}
