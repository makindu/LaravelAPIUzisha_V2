<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderspaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providerspayments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('done_by')->unsigned();
            $table->foreign('done_by')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('provider_id')->unsigned();
            $table->foreign('provider_id')->references('id')->on('provider_controllers')->onDelete('cascade');
            $table->bigInteger('stock_history_id')->unsigned();
            $table->foreign('stock_history_id')->references('id')->on('stock_history_controllers')->onDelete('cascade');
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->string('note')->nullable();
            $table->double('amount');
            $table->uuid('uuid')->default(DB::raw('(UUID())'));
            $table->dateTimeTz('done_at')->default(now());
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
        Schema::dropIfExists('providerspayments');
    }
}
