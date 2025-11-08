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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->string('title')->nullable();
            $table->longText('content');
            $table->string('content_hash', 64)->index();
            $table->integer('char_count')->default(0);
            $table->integer('line_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->string('password_hash')->nullable();
            $table->integer('view_limit')->nullable();
            $table->integer('views')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_reported')->default(false);
            $table->boolean('is_public')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('forked_from_id')->nullable()->constrained('notes')->nullOnDelete();
            $table->string('ip_address', 64)->nullable(); // SHA256 hash length
            $table->timestamps();

            // Additional indexes
            $table->index('user_id');
            $table->index('expires_at');
            $table->index('created_at');
            $table->index(['is_active', 'is_reported']);
            $table->index(['view_limit', 'views']);
            // Note: fullText index on title, content removed for SQLite compatibility
            // Add manually for MySQL: ALTER TABLE notes ADD FULLTEXT(title, content);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
