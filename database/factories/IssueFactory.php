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
        return [
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraphs(2, true),
            'priority' => $this->faker->randomElement(IssuePriority::cases()),
            'category' => $this->faker->randomElement(IssueCategory::cases()),
            'status' => IssueStatus::Open,
            'summary_status' => SummaryStatus::Pending,
            'is_escalated' => false,
        ];
    }

    public function withSummary(): static
    {
        return $this->state(fn () => [
            'summary_status' => SummaryStatus::Ready,
            'summary' => $this->faker->sentence(10),
            'next_action' => $this->faker->sentence(8),
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
