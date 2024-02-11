<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnterprisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enterprises', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();	
            $table->string('description')->nullable();	
	        $table->string('rccm')->nullable();	
	        $table->string('national_identification')->nullable();
            $table->string('num_impot')->nullable();	
	        $table->string('autorisation_fct')->nullable();
            $table->string('adresse')->nullable();	
	        $table->string('phone')->nullable()->unique();
            $table->string('mail')->nullable()->unique();
	        $table->string('website')->nullable();
	        $table->string('facebook')->nullable();
	        $table->string('instagram')->nullable();
	        $table->string('linkdin')->nullable();
	        $table->string('logo')->nullable();	
	        $table->string('category')->nullable();	   
	        $table->double('vat_rate')->nullable();	
	        $table->string('uuid')->nullable();	
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
	        $table->string('sync_status')->nullable();
	        $table->string('status');
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
        Schema::dropIfExists('enterprises');
    }
}
