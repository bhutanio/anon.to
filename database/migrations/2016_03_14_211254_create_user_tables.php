<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('forgot')->unsigned()->default(false);
            $table->string('forgot_token')->nullable()->default(null);
            $table->timestamp('forgot_at')->nullable()->default(null);
            $table->timestamps();
        });

        DB::unprepared("INSERT INTO `users` (`id`, `username`, `email`, `password`, `remember_token`, `forgot`, `forgot_token`, `forgot_at`, `created_at`, `updated_at`) VALUES (NULL, 'anonymous', 'anonymous@anon.to', '', NULL, '0', NULL, NULL, CURRENT_TIME(), CURRENT_TIME())");

        DB::unprepared("INSERT INTO `users` (`id`, `username`, `email`, `password`, `remember_token`, `forgot`, `forgot_token`, `forgot_at`, `created_at`, `updated_at`) VALUES (NULL, 'admin', 'admin@anon.to', '', NULL, '0', NULL, NULL, CURRENT_TIME(), CURRENT_TIME())");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('users');
    }
}
