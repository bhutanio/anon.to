<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('id');
            $table->boolean('is_admin')->default(false)->after('remember_token');
            $table->boolean('is_verified')->default(false)->after('is_admin');
            $table->unsignedBigInteger('api_rate_limit')->default(100)->after('is_verified');
            $table->timestamp('last_login_at')->nullable()->after('api_rate_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'is_admin',
                'is_verified',
                'api_rate_limit',
                'last_login_at',
            ]);
        });
    }
};
