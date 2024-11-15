<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWekagroupmembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wekagroupmembers', function (Blueprint $table) {
            $table->id();
            $table->string('level')->default('member');
            $table->bigInteger('done_by_id')->unsigned();
            $table->foreign('done_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('member_id')->unsigned();
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->bigInteger('group_id')->unsigned();
            $table->foreign('group_id')->references('id')->on('wekagroups')->onDelete('cascade');
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
        Schema::dropIfExists('wekagroupmembers');
    }
}
