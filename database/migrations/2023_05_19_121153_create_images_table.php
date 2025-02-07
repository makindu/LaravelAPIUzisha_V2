<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('doc_link');
            $table->string('description')->nullable();
            $table->string('type_operation');
            $table->string('status')->default('available');
            $table->integer('ref_operation');
            $table->bigInteger('done_by')->unsigned();
            $table->foreign('done_by')->references('id')->on('users');
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises');
            $table->double('size')->nullable();
            $table->boolean('principal')->default(false);
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
        Schema::dropIfExists('images');
    }
}
