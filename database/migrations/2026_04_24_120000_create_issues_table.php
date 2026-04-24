<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->enum('category', [
                'billing', 'account', 'technical', 'access',
                'general_inquiry', 'feedback', 'other',
            ]);
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');

            $table->string('summary', 500)->nullable();
            $table->string('next_action', 500)->nullable();
            $table->enum('summary_status', ['pending', 'ready', 'failed'])->default('pending');

            $table->boolean('is_escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->string('escalation_reason')->nullable();

            $table->timestamp('due_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('category');
            $table->index('priority');
            $table->index('is_escalated');
            $table->index(['status', 'priority', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
