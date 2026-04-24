<?php

namespace Tests\Unit;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\SummaryStatus;
use App\Models\Issue;
use App\Services\Escalation\EscalationEvaluator;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EscalationEvaluatorTest extends TestCase
{
    public function test_critical_and_open_escalates_immediately(): void
    {
        $issue = $this->makeIssue(
            priority: IssuePriority::Critical,
            status: IssueStatus::Open,
            createdAt: Carbon::now(),
        );

        $decision = (new EscalationEvaluator())->evaluate($issue);

        $this->assertTrue($decision->shouldEscalate);
        $this->assertSame(EscalationEvaluator::REASON_CRITICAL_OPEN, $decision->reason);
    }

    public function test_high_priority_open_beyond_24h_escalates(): void
    {
        $issue = $this->makeIssue(
            priority: IssuePriority::High,
            status: IssueStatus::InProgress,
            createdAt: Carbon::now()->subHours(25),
        );

        $decision = (new EscalationEvaluator())->evaluate($issue);

        $this->assertTrue($decision->shouldEscalate);
        $this->assertSame(EscalationEvaluator::REASON_HIGH_AGED, $decision->reason);
    }

    public function test_overdue_non_resolved_issue_escalates(): void
    {
        $issue = $this->makeIssue(
            priority: IssuePriority::Medium,
            status: IssueStatus::InProgress,
            createdAt: Carbon::now(),
            dueAt: Carbon::now()->subHour(),
        );

        $decision = (new EscalationEvaluator())->evaluate($issue);

        $this->assertTrue($decision->shouldEscalate);
        $this->assertSame(EscalationEvaluator::REASON_OVERDUE, $decision->reason);
    }

    public function test_fresh_low_priority_issue_does_not_escalate(): void
    {
        $issue = $this->makeIssue(
            priority: IssuePriority::Low,
            status: IssueStatus::Open,
            createdAt: Carbon::now(),
        );

        $decision = (new EscalationEvaluator())->evaluate($issue);

        $this->assertFalse($decision->shouldEscalate);
        $this->assertNull($decision->reason);
    }

    private function makeIssue(
        IssuePriority $priority,
        IssueStatus $status,
        Carbon $createdAt,
        ?Carbon $dueAt = null,
    ): Issue {
        $issue = new Issue();
        $issue->setRawAttributes([
            'title' => 'example',
            'description' => 'an example issue used for escalation evaluator tests.',
            'priority' => $priority->value,
            'category' => 'other',
            'status' => $status->value,
            'summary_status' => SummaryStatus::Pending->value,
            'is_escalated' => false,
            'due_at' => $dueAt?->toDateTimeString(),
            'created_at' => $createdAt->toDateTimeString(),
            'updated_at' => $createdAt->toDateTimeString(),
        ]);

        return $issue;
    }
}
