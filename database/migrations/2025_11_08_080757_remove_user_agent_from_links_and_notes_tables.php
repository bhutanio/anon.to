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
        Schema::table('links', function (Blueprint $table) {
            if (Schema::hasColumn('links', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
        });

        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->text('user_agent')->nullable();
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->text('user_agent')->nullable();
        });
    }
};
