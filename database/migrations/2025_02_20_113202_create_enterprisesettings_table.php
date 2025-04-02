<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnterprisesettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enterprisesettings', function (Blueprint $table) {
            $table->id();
            $table->string('limit_storage')->default('limited');
            $table->double('storage')->default(100000);
            $table->string('limit_users')->default('limited');
            $table->double('nbr_users')->default(2);
            $table->string('limit_deposits')->default('limited');
            $table->double('nbr_deposits')->default(1);
            $table->string('limit_invoices')->default('limited');
            $table->double('nbr_invoices')->default(100);
            $table->string('limit_services')->default('limited');
            $table->double('nbr_services')->default(100);
            $table->string('limit_customers')->default('limited');
            $table->double('nbr_customers')->default(100);
            $table->string('limit_funds')->default('limited');
            $table->double('nbr_funds')->default(1);
            $table->string('limit_expenditures')->default('limited');
            $table->double('nbr_expenditures')->default(100);
            $table->string('limit_accounts')->default('limited');
            $table->double('nbr_accounts')->default(100); 
            $table->string('limit_providers')->default('limited');
            $table->double('nbr_providers')->default(100);
            $table->string('licence_type')->default('limited');
            $table->dateTime('licence_from')->default(now());
            $table->dateTime('licence_to')->default(now()->addMonth());
            $table->string('limit_sms')->default('limited');
            $table->double('nbr_sms')->default(0);
            $table->boolean('whatsapp_activation')->default(false);
            $table->string('whatsapp_api')->nullable();
            $table->string('whatsapp_token')->nullable();
            $table->string('limit_pos')->default('limited');
            $table->double('nbr_pos')->default(1);
            $table->string('language')->default("fr");
            $table->bigInteger('plan_id')->unsigned()->nullable();
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade'); 
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->enum('mode',['test','plan',''])->default('test');
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
        Schema::dropIfExists('enterprisesettings');
    }
}
