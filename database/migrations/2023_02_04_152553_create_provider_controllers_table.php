<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderControllersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_controllers', function (Blueprint $table) {
            $table->id();
            $table->integer('pos_id')->nullable();
            $table->bigInteger('created_by_id')->unsigned();
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('providerName');
            $table->string('adress')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->string('type')->nullable();
            $table->string('mail')->nullable();
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
        Schema::dropIfExists('provider_controllers');
    }
}
