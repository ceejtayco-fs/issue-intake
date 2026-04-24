<?php

namespace Tests\Feature;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\SummaryStatus;
use App\Jobs\GenerateIssueSummary;
use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class IssueUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_update_changes_only_supplied_fields(): void
    {
        Queue::fake();

        $issue = Issue::factory()->create([
            'priority' => IssuePriority::Low,
            'status' => IssueStatus::Open,
            'summary_status' => SummaryStatus::Ready,
            'summary' => 'original summary',
            'next_action' => 'original action',
        ]);

        $response = $this->patchJson("/api/issues/{$issue->id}", [
            'status' => 'in_progress',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.priority', 'low')
            ->assertJsonPath('data.summary_status', 'ready')
            ->assertJsonPath('data.summary', 'original summary');

        Queue::assertNothingPushed();
    }

    public function test_changing_description_reverts_summary_to_pending_and_redispatches_job(): void
    {
        Queue::fake();

        $issue = Issue::factory()->create([
            'description' => 'Original description that is definitely long enough to pass validation.',
            'summary_status' => SummaryStatus::Ready,
            'summary' => 'old summary',
        ]);

        $response = $this->patchJson("/api/issues/{$issue->id}", [
            'description' => 'A completely new description that is also long enough to pass validation and triggers regeneration.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.summary_status', 'pending');

        Queue::assertPushed(GenerateIssueSummary::class);
    }
}
