<?php

namespace Tests\Feature;

use App\Enums\IssueCategory;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filters_by_status_category_and_priority(): void
    {
        Issue::factory()->create([
            'priority' => IssuePriority::High,
            'category' => IssueCategory::Billing,
            'status' => IssueStatus::Open,
        ]);
        Issue::factory()->create([
            'priority' => IssuePriority::Low,
            'category' => IssueCategory::Billing,
            'status' => IssueStatus::Open,
        ]);
        Issue::factory()->create([
            'priority' => IssuePriority::High,
            'category' => IssueCategory::Technical,
            'status' => IssueStatus::Open,
        ]);

        $response = $this->getJson('/api/issues?priority=high&category=billing&status=open');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.priority', 'high')
            ->assertJsonPath('data.0.category', 'billing');
    }

    public function test_soft_deleted_issues_are_excluded_by_default_and_visible_with_trashed_only(): void
    {
        $kept = Issue::factory()->create(['title' => 'still here']);
        $trashed = Issue::factory()->create(['title' => 'gone']);
        $trashed->delete();

        $default = $this->getJson('/api/issues');
        $default->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $kept->id);

        $onlyTrashed = $this->getJson('/api/issues?trashed=only');
        $onlyTrashed->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $trashed->id);
    }

    public function test_web_index_defaults_to_open_issues_only(): void
    {
        $open = Issue::factory()->create(['status' => IssueStatus::Open]);
        Issue::factory()->create(['status' => IssueStatus::Resolved]);
        Issue::factory()->create(['status' => IssueStatus::InProgress]);

        $response = $this->get('/issues');
        $response->assertOk();

        $issues = $response->viewData('issues')->getCollection();

        $this->assertCount(1, $issues);
        $this->assertSame($open->id, $issues->first()->id);
        $this->assertSame(IssueStatus::Open, $issues->first()->status);
    }

}
