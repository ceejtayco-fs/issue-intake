<?php

namespace App\Services\Summarization;

final readonly class SummaryResult
{
    public function __construct(
        public string $summary,
        public string $nextAction,
        public string $driver,
        public ?string $model = null,
        public ?int $latencyMs = null,
        public ?int $promptTokens = null,
        public ?int $completionTokens = null,
    ) {
    }
}
