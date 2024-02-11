<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointOfSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_of_sales', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type');
            $table->double('sold')->nullable();
            $table->double('nb_sales_bonus')->nullable();
            $table->double('bonus_percentage')->nullable();
            $table->double('workforce_percent')->nullable();
            $table->string('rccm')->nullable();	
	        $table->string('national_identification')->nullable();
            $table->string('num_impot')->nullable();	
	        $table->string('autorisation_fct')->nullable();
            $table->string('adresse')->nullable();	
	        $table->string('phone')->nullable();
            $table->string('mail')->nullable();
	        $table->string('website')->nullable();	
	        $table->string('logo')->nullable();	
	        $table->string('category')->nullable();	   
	        $table->double('vat_rate')->nullable();	
	        $table->string('uuid')->nullable();	
	        $table->string('status')->nullable();	
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
        Schema::dropIfExists('point_of_sales');
    }
}
