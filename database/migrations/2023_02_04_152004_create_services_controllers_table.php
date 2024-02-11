<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesControllersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_controllers', function (Blueprint $table) {
            $table->id();
            $table->integer('uom_id')->nullable();	
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('category_id')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type');
            $table->string('codebar')->nullable();
            $table->string('code_manuel')->nullable();
            $table->string('photo')->nullable();
            $table->double('point')->nullable();
            $table->double('nbrgros')->nullable();
            $table->boolean('bonus_applicable')->nullable();
            $table->boolean('has_vat')->nullable();
            $table->double('tva')->nullable();
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
        Schema::dropIfExists('services_controllers');
    }
}
