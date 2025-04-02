<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnterprisesinvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enterprisesinvoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 5)->default('USD');
            $table->dateTime('invoice_date'); // Date de la facture
            $table->dateTime('due_date')->nullable (); // Date limite de paiement
            $table->enum('status', ['pending', 'paid', 'failed', 'canceled'])->default('pending');
            $table->string('payment_method')->nullable(); // Ex: Stripe, PayPal, Virement
            $table->json('details')->nullable(); // Stocke des infos supplémentaires (ex: TVA, réductions)
            $table->string('uuid');
            $table->string('description')->nullable();
            $table->json('payments')->nullable();
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
        Schema::dropIfExists('enterprisesinvoices');
    }
}
