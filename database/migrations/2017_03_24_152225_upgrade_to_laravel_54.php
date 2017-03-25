<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpgradeToLaravel54 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('_jobs');
        Schema::dropIfExists('_jobs_failed');
        Schema::dropIfExists('_sessions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['forgot', 'forgot_token', 'forgot_at']);
            $table->boolean('active')->unsigned()->default(false)->after('password');
        });

        DB::table('users')->where('id', 2)->update(['active' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('forgot')->unsigned()->default(false)->after('remember_token');
            $table->string('forgot_token')->nullable()->default(null)->after('forgot');
            $table->timestamp('forgot_at')->nullable()->default(null)->after('forgot');
        });

        Schema::create('_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue');
            $table->longText('payload');
            $table->tinyInteger('attempts')->unsigned();
            $table->tinyInteger('reserved')->unsigned();
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
            $table->index(['queue', 'reserved', 'reserved_at']);
        });

        Schema::create('_jobs_failed', function (Blueprint $table) {
            $table->increments('id');
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('_sessions', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->integer('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
        });
    }
}
