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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->morphs('reportable');
            $table->string('category', 50);
            $table->text('url')->nullable();
            $table->string('email')->nullable();
            $table->text('comment');
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('dealt_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dealt_at')->nullable();
            $table->timestamps();

            // Additional indexes (morphs() already creates reportable index)
            $table->index(['status', 'created_at']);
            $table->index('user_id');
            $table->index('dealt_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
