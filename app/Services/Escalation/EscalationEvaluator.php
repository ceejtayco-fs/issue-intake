<?php

namespace App\Services\Escalation;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Issue;
use Illuminate\Support\Carbon;

class EscalationEvaluator
{
    public const REASON_CRITICAL_OPEN = 'critical_open';
    public const REASON_HIGH_AGED = 'high_priority_active_over_24h';
    public const REASON_OVERDUE = 'overdue_and_not_resolved';

    public function evaluate(Issue $issue, ?Carbon $now = null): EscalationDecision
    {
        $now ??= Carbon::now();

        if ($issue->priority === IssuePriority::Critical && $issue->status === IssueStatus::Open) {
            return EscalationDecision::because(self::REASON_CRITICAL_OPEN);
        }

        if (
            $issue->priority->isHighOrAbove()
            && $issue->status->isActive()
            && $issue->created_at !== null
            && $issue->created_at->lt($now->copy()->subHours(24))
        ) {
            return EscalationDecision::because(self::REASON_HIGH_AGED);
        }

        if (
            $issue->due_at !== null
            && $issue->due_at->lt($now)
            && ! in_array($issue->status, [IssueStatus::Resolved, IssueStatus::Closed], true)
        ) {
            return EscalationDecision::because(self::REASON_OVERDUE);
        }

        return EscalationDecision::none();
    }

    public function apply(Issue $issue, ?Carbon $now = null): bool
    {
        $decision = $this->evaluate($issue, $now);

        if ($decision->shouldEscalate && ! $issue->is_escalated) {
            $issue->forceFill([
                'is_escalated' => true,
                'escalated_at' => $now ?? Carbon::now(),
                'escalation_reason' => $decision->reason,
            ])->save();

            return true;
        }

        return false;
    }
}
