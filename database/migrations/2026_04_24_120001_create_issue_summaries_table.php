<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')
                ->constrained('issues')
                ->cascadeOnDelete();

            $table->string('driver', 32);
            $table->string('model', 64)->nullable();

            $table->string('summary', 500)->nullable();
            $table->string('next_action', 500)->nullable();

            $table->enum('status', ['succeeded', 'failed']);

            $table->unsignedInteger('latency_ms')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->text('error')->nullable();

            $table->timestamps();

            $table->index(['issue_id', 'created_at']);
            $table->index(['issue_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_summaries');
    }
};
