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
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->string('slug')->unique()->nullable();
            $table->string('url_scheme', 10);
            $table->string('url_host');
            $table->integer('url_port')->nullable();
            $table->text('url_path')->nullable();
            $table->text('url_query')->nullable();
            $table->text('url_fragment')->nullable();
            $table->text('full_url');
            $table->string('full_url_hash', 64)->index();
            $table->string('title', 500)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('password_hash')->nullable();
            $table->unsignedBigInteger('visits')->default(0);
            $table->unsignedBigInteger('unique_visits')->default(0);
            $table->timestamp('last_visited_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_reported')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Additional indexes
            $table->index('user_id');
            $table->index('expires_at');
            $table->index('created_at');
            $table->index('visits');
            $table->index(['is_active', 'is_reported']);
            // Note: fullText index on url_host removed for SQLite compatibility
            // Add manually for MySQL: ALTER TABLE links ADD FULLTEXT(url_host);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
