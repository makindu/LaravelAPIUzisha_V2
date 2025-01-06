<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUzishafuelconsumptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uzishafuelconsumptions', function (Blueprint $table) {
            $table->id();
            $table->double('quantity');
            $table->double('price')->default(0);
            $table->string('pump');
            $table->string('num')->nullable();
            $table->string('station')->nullable();
            $table->date('done_at');
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
        Schema::dropIfExists('uzishafuelconsumptions');
    }
}
