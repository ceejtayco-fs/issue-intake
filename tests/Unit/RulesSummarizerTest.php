<?php

namespace Tests\Unit;

use App\Enums\IssueCategory;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\SummaryStatus;
use App\Models\Issue;
use App\Services\Summarization\RulesSummarizer;
use PHPUnit\Framework\TestCase;

class RulesSummarizerTest extends TestCase
{
    public function test_summary_includes_priority_and_category_tags(): void
    {
        $issue = $this->makeIssue(
            priority: IssuePriority::High,
            category: IssueCategory::Billing,
            description: 'Customer was charged twice for the same order. Needs refund.',
        );

        $result = (new RulesSummarizer())->summarize($issue);

        $this->assertStringContainsString('high', $result->summary);
        $this->assertStringContainsString('billing', $result->summary);
        $this->assertSame('rules', $result->driver);
        $this->assertSame('rules-v1', $result->model);
        $this->assertGreaterThanOrEqual(1, $result->latencyMs);
    }

    public function test_next_action_prefix_varies_by_priority(): void
    {
        $critical = (new RulesSummarizer())->summarize(
            $this->makeIssue(priority: IssuePriority::Critical, category: IssueCategory::Technical, description: 'Outage on orders API.')
        );
        $low = (new RulesSummarizer())->summarize(
            $this->makeIssue(priority: IssuePriority::Low, category: IssueCategory::Feedback, description: 'Would like dark mode in the dashboard.')
        );

        $this->assertStringStartsWith('Immediately', $critical->nextAction);
        $this->assertStringStartsWith('When capacity allows', $low->nextAction);
    }

    private function makeIssue(IssuePriority $priority, IssueCategory $category, string $description): Issue
    {
        $issue = new Issue();
        $issue->setRawAttributes([
            'title' => 'unused in rules summarizer',
            'description' => $description,
            'priority' => $priority->value,
            'category' => $category->value,
            'status' => IssueStatus::Open->value,
            'summary_status' => SummaryStatus::Pending->value,
            'is_escalated' => false,
        ]);

        return $issue;
    }
}
