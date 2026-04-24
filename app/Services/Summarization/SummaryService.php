<?php

namespace App\Services\Summarization;

use App\Enums\SummaryStatus;
use App\Models\Issue;
use App\Models\IssueSummary;
use Illuminate\Support\Facades\Log;
use Throwable;

class SummaryService
{
    public function __construct(
        private readonly GroqSummarizer $groq,
        private readonly RulesSummarizer $rules,
    ) {
    }

    public function generate(Issue $issue): IssueSummary
    {
        $primary = $this->resolvePrimary();

        if ($primary === $this->rules) {
            return $this->runAndRecord($issue, $this->rules);
        }

        try {
            return $this->runAndRecord($issue, $primary);
        } catch (Throwable $e) {
            Log::warning('Primary summarizer failed; falling back to rules.', [
                'issue_id' => $issue->id,
                'driver' => $primary->name(),
                'error' => $e->getMessage(),
            ]);

            $this->recordFailure($issue, $primary, $e);

            return $this->runAndRecord($issue, $this->rules);
        }
    }

    private function resolvePrimary(): SummarizerInterface
    {
        $driver = config('summarization.driver', 'rules');

        if ($driver === 'groq' && ! empty(config('summarization.groq.api_key'))) {
            return $this->groq;
        }

        return $this->rules;
    }

    private function runAndRecord(Issue $issue, SummarizerInterface $driver): IssueSummary
    {
        $result = $driver->summarize($issue);

        $record = IssueSummary::create([
            'issue_id' => $issue->id,
            'driver' => $result->driver,
            'model' => $result->model,
            'summary' => $result->summary,
            'next_action' => $result->nextAction,
            'status' => 'succeeded',
            'latency_ms' => $result->latencyMs,
            'prompt_tokens' => $result->promptTokens,
            'completion_tokens' => $result->completionTokens,
        ]);

        $issue->forceFill([
            'summary' => $result->summary,
            'next_action' => $result->nextAction,
            'summary_status' => SummaryStatus::Ready,
        ])->save();

        return $record;
    }

    private function recordFailure(Issue $issue, SummarizerInterface $driver, Throwable $e): void
    {
        IssueSummary::create([
            'issue_id' => $issue->id,
            'driver' => $driver->name(),
            'model' => $driver->model(),
            'summary' => null,
            'next_action' => null,
            'status' => 'failed',
            'error' => mb_substr($e->getMessage(), 0, 1000),
        ]);
    }
}
