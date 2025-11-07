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
        Schema::create('allow_lists', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->enum('type', ['allow', 'block']);
            $table->enum('pattern_type', ['exact', 'wildcard', 'regex']);
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('hit_count')->default(0);
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Additional indexes
            $table->unique(['domain', 'type']);
            $table->index(['type', 'is_active']);
            $table->index('added_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allow_lists');
    }
};
