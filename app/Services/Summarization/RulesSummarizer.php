<?php

namespace App\Services\Summarization;

use App\Enums\IssueCategory;
use App\Enums\IssuePriority;
use App\Models\Issue;

class RulesSummarizer implements SummarizerInterface
{
    private const MODEL_TAG = 'rules-v1';

    public function summarize(Issue $issue): SummaryResult
    {
        $start = microtime(true);

        $summary = $this->buildSummary($issue);
        $nextAction = $this->buildNextAction($issue);

        $latency = (int) round((microtime(true) - $start) * 1000);

        return new SummaryResult(
            summary: $summary,
            nextAction: $nextAction,
            driver: $this->name(),
            model: $this->model(),
            latencyMs: max($latency, 1),
        );
    }

    public function name(): string
    {
        return 'rules';
    }

    public function model(): ?string
    {
        return self::MODEL_TAG;
    }

    private function buildSummary(Issue $issue): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $issue->description));
        $firstSentence = $this->firstSentence($normalized);
        $prefix = sprintf('[%s/%s]', $issue->priority->value, $issue->category->value);

        return $this->truncate("{$prefix} {$firstSentence}", 500);
    }

    private function buildNextAction(Issue $issue): string
    {
        return match ($issue->category) {
            IssueCategory::Billing => $this->priorityPrefix($issue)
                . 'review recent transactions for this account, verify the reported discrepancy, and issue a refund or correction if warranted.',
            IssueCategory::Account => $this->priorityPrefix($issue)
                . 'confirm account ownership, capture the specific request in writing, and route to the account operations queue.',
            IssueCategory::Technical => $this->priorityPrefix($issue)
                . 'reproduce the behaviour, capture logs and request/response samples, then escalate to engineering with a minimal repro.',
            IssueCategory::Access => $this->priorityPrefix($issue)
                . 'verify the user\'s identity, check their role and permission assignments, and either restore access or explain the restriction.',
            IssueCategory::GeneralInquiry => $this->priorityPrefix($issue)
                . 'acknowledge receipt within one business day and route to the appropriate team based on the question topic.',
            IssueCategory::Feedback => $this->priorityPrefix($issue)
                . 'thank the submitter, log the feedback in the product backlog, and notify the relevant product owner.',
            IssueCategory::Other => $this->priorityPrefix($issue)
                . 'triage to identify the correct category and route accordingly; follow up with the submitter if clarification is needed.',
        };
    }

    private function priorityPrefix(Issue $issue): string
    {
        return match ($issue->priority) {
            IssuePriority::Critical => 'Immediately ',
            IssuePriority::High => 'Within the next business day, ',
            IssuePriority::Medium => 'As soon as reasonably possible, ',
            IssuePriority::Low => 'When capacity allows, ',
        };
    }

    private function firstSentence(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $match = preg_match('/^(.{10,240}?[.!?])(?:\s|$)/u', $text, $m);
        if ($match === 1) {
            return $m[1];
        }

        return $this->truncate($text, 240);
    }

    private function truncate(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit - 1) . '…';
    }
}
