<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');
            $table->string('full_name')->nullable();
            $table->string('user_mail')->nullable();
            $table->timestamp('email_verified_at');
            $table->string('user_phone')->nullable();
            $table->string('user_password');
            $table->string('user_type');
            $table->string('status');
            $table->integer('permissions')->nullable();
            $table->string('note')->nullable();
            $table->string('avatar')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
