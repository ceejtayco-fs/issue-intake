<?php

namespace App\Jobs;

use App\Enums\SummaryStatus;
use App\Models\Issue;
use App\Services\Summarization\SummaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateIssueSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(public readonly int $issueId)
    {
    }

    public function backoff(): array
    {
        return [5, 30, 120];
    }

    public function handle(SummaryService $service): void
    {
        $issue = Issue::find($this->issueId);

        if ($issue === null) {
            Log::info('GenerateIssueSummary skipped: issue not found.', ['issue_id' => $this->issueId]);

            return;
        }

        $service->generate($issue);
    }

    public function failed(?Throwable $e): void
    {
        Log::error('GenerateIssueSummary exhausted retries.', [
            'issue_id' => $this->issueId,
            'error' => $e?->getMessage(),
        ]);

        $issue = Issue::find($this->issueId);
        if ($issue === null) {
            return;
        }

        // Last-resort fallback so the issue is not left blank.
        try {
            app(SummaryService::class)->generate($issue);
        } catch (Throwable $finalError) {
            $issue->forceFill(['summary_status' => SummaryStatus::Failed])->save();

            Log::error('GenerateIssueSummary final fallback also failed.', [
                'issue_id' => $this->issueId,
                'error' => $finalError->getMessage(),
            ]);
        }
    }
}
