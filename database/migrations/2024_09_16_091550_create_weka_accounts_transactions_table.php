<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWekaAccountsTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weka_accounts_transactions', function (Blueprint $table) {
            $table->id();
            $table->double('amount');
            $table->double('sold_before')->nullable();
            $table->double('sold_after')->nullable();
            $table->string('type');
            $table->string('motif')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');  
            $table->bigInteger('member_id')->unsigned();
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('member_account_id')->unsigned();
            $table->foreign('member_account_id')->references('id')->on('wekamemberaccounts')->onDelete('cascade');
            $table->bigInteger('enterprise_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->date('done_at')->nullable();
            $table->integer('account_id')->nullable();
            $table->integer('sync_status')->default(1);
            $table->string('operation_done_by')->nullable();
            $table->string('uuid')->nullable();
            $table->double('fees')->default(0);
            $table->string('transaction_status')->default('pending');
            $table->string('phone')->nullable();
            $table->string('adresse')->nullable();
            $table->boolean('self_operation')->default(true);
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
        Schema::dropIfExists('weka_accounts_transactions');
    }
}
