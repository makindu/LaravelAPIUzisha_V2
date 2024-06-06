<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiculesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();	
            $table->bigInteger('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customer_controllers')->onDelete('cascade');	
            $table->string('numero_immatriculation')->unique();	
            $table->string('annee_fabrication')->nullable();		
            $table->string('date_mise_en_circulation')->nullable();		
            $table->string('genre')->nullable();
            $table->string('marque');	
            $table->string('type_ou_modele')->nullable();	
            $table->string('puissance')->nullable();
            $table->string('numero_dans_la_serie')->nullable();
            $table->string('energie')->nullable();
            $table->string('kilometrage')->nullable();			
            $table->string('usage_vehicule')->nullable();
            $table->string('couleur')->nullable();
            $table->string('numero_chassis')->nullable();
            $table->string('numero_moteur')->nullable();		
            $table->integer('updated_by')->nullable();
            $table->string('uuid');
            $table->bigInteger('created_by_id')->unsigned();
            $table->foreign('created_by_id')->references('id')->on('users');
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
        Schema::dropIfExists('vehicules');
    }
}
