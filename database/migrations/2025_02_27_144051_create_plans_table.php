<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description');
            $table->string('slug')->unique()->nullable();
            $table->json('features')->nullable();
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->string('color')->nullable()->unique();
            $table->decimal('price')->unique();
            $table->string('support_type')->default('email');
            $table->string('currency')->default('USD');
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->string('stripe_price_id')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('is_popular')->default(0);
            $table->string('others')->nullable();
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
        Schema::dropIfExists('plans');
    }
}
