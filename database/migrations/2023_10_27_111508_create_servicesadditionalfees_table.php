<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesadditionalfeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicesadditionalfees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services_controllers');
            $table->double('price');
            $table->boolean('tax_exempt')->nullable();
            $table->string('calculation')->default('amount');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('servicesadditionalfees');
    }
}
