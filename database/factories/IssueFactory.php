<?php

namespace Database\Factories;

use App\Enums\IssueCategory;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\SummaryStatus;
use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    protected $model = Issue::class;

    public function definition(): array
    {
        $samples = [
            [
                'title' => 'Customer cannot download monthly invoice',
                'description' => 'Customer can view invoice details in the portal but receives an error when trying to download the PDF copy for accounting.',
            ],
            [
                'title' => 'Manager approval button missing for leave requests',
                'description' => 'Team managers report that the approval action is no longer visible in the HR module after the latest permission update.',
            ],
            [
                'title' => 'Intermittent timeout on report export endpoint',
                'description' => 'Large report exports sometimes fail with a timeout after about 40 seconds, especially during peak business hours.',
            ],
            [
                'title' => 'User profile update not saving mobile number',
                'description' => 'Users can edit their profile, but the mobile number field reverts to the previous value after pressing save.',
            ],
            [
                'title' => 'Delayed email notifications for password resets',
                'description' => 'Password reset emails are delivered 20 to 30 minutes late, causing users to request multiple reset links.',
            ],
            [
                'title' => 'Refund request for canceled annual plan',
                'description' => 'Customer canceled within the refund window but was still charged the annual renewal fee and is asking for a reversal.',
            ],
            [
                'title' => 'Search results show archived records unexpectedly',
                'description' => 'Active search filters are returning archived records even when users select only active items in the filter panel.',
            ],
        ];

        $sample = $this->faker->randomElement($samples);

        return [
            'title' => $sample['title'],
            'description' => $sample['description'],
            'priority' => $this->faker->randomElement(IssuePriority::cases()),
            'category' => $this->faker->randomElement(IssueCategory::cases()),
            'status' => IssueStatus::Open,
            'summary_status' => SummaryStatus::Pending,
            'is_escalated' => false,
        ];
    }

    public function withSummary(): static
    {
        $summarySamples = [
            [
                'summary' => 'Customer cannot complete an expected workflow due to a blocking application issue.',
                'next_action' => 'Reproduce the issue, confirm scope of impact, and apply the corresponding product or support fix.',
            ],
            [
                'summary' => 'A permission or access configuration problem is preventing users from performing required actions.',
                'next_action' => 'Review role mappings for affected users and restore the missing permissions after validation.',
            ],
            [
                'summary' => 'Billing workflow produced an incorrect customer-facing outcome that needs correction.',
                'next_action' => 'Validate transaction history, correct the billing record, and communicate the resolution timeline.',
            ],
            [
                'summary' => 'System performance degradation is causing delayed or failed processing for user requests.',
                'next_action' => 'Inspect logs and query performance, apply mitigation, and monitor for recurrence.',
            ],
        ];

        $sample = $this->faker->randomElement($summarySamples);

        return $this->state(fn () => [
            'summary_status' => SummaryStatus::Ready,
            'summary' => $sample['summary'],
            'next_action' => $sample['next_action'],
        ]);
    }

    public function escalated(): static
    {
        return $this->state(fn () => [
            'is_escalated' => true,
            'escalated_at' => now()->subHours(2),
            'escalation_reason' => 'high_priority_open_over_24h',
            'priority' => IssuePriority::High,
            'status' => IssueStatus::Open,
        ]);
    }

    public function priority(IssuePriority $priority): static
    {
        return $this->state(fn () => ['priority' => $priority]);
    }

    public function category(IssueCategory $category): static
    {
        return $this->state(fn () => ['category' => $category]);
    }

    public function status(IssueStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
