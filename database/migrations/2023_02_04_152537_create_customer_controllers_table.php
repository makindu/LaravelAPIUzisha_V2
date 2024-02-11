<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerControllersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_controllers', function (Blueprint $table) {
            $table->id();
            $table->integer('pos_id')->nullable();
            $table->integer('created_by_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('customerName');
            $table->string('marital_status')->nullable();
            $table->string('other_contact')->nullable();
            $table->string('adress')->nullable();
            $table->string('phone')->nullable();
            $table->string('mail')->nullable();
            $table->integer('employer')->nullable();
            $table->string('type');
            $table->string('uuid');
            $table->string('sex')->nullable();
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
        Schema::dropIfExists('customer_controllers');
    }
}
