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
        Schema::create('link_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained()->cascadeOnDelete();
            $table->timestamp('visited_at');
            $table->string('ip_address', 45)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');

            // Additional indexes
            $table->index(['link_id', 'visited_at']);
            $table->index('country_code');
            $table->index('visited_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_analytics');
    }
};
