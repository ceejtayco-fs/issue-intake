<?php

namespace App\Console\Commands;

use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Services\Escalation\EscalationEvaluator;
use Illuminate\Console\Command;

class EvaluateEscalationsCommand extends Command
{
    protected $signature = 'issues:evaluate-escalations';

    protected $description = 'Evaluate every active issue against the escalation rules and flag those that qualify.';

    public function handle(EscalationEvaluator $evaluator): int
    {
        $escalated = 0;
        $examined = 0;

        Issue::query()
            ->whereIn('status', [IssueStatus::Open, IssueStatus::InProgress])
            ->where('is_escalated', false)
            ->chunkById(200, function ($issues) use ($evaluator, &$escalated, &$examined) {
                foreach ($issues as $issue) {
                    $examined++;
                    if ($evaluator->apply($issue)) {
                        $escalated++;
                    }
                }
            });

        $this->info("Examined {$examined} active issues. Newly escalated: {$escalated}.");

        return self::SUCCESS;
    }
}
