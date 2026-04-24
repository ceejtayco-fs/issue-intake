<?php

namespace Tests\Feature;

use App\Jobs\GenerateIssueSummary;
use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class IssueCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_issue_persists_it_and_queues_summary_generation(): void
    {
        Queue::fake();

        $payload = [
            'title' => 'Payment failed on checkout',
            'description' => 'When I click "Pay now" I see an error page saying something went wrong. I tried three times with the same card.',
            'priority' => 'high',
            'category' => 'billing',
        ];

        $response = $this->postJson('/api/issues', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.category', 'billing')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.summary_status', 'pending');

        $this->assertDatabaseHas('issues', [
            'title' => $payload['title'],
            'priority' => 'high',
            'category' => 'billing',
        ]);

        Queue::assertPushed(GenerateIssueSummary::class);
    }

    public function test_short_description_is_rejected_with_422_and_field_error(): void
    {
        $response = $this->postJson('/api/issues', [
            'title' => 'ok',
            'description' => 'too short',
            'priority' => 'low',
            'category' => 'other',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description']);
    }

    public function test_invalid_enum_values_are_rejected(): void
    {
        $response = $this->postJson('/api/issues', [
            'title' => 'Valid title',
            'description' => 'This description is long enough to pass the minimum length validation.',
            'priority' => 'urgent',
            'category' => 'misc',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority', 'category']);
    }

    public function test_critical_open_issue_is_escalated_immediately(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/issues', [
            'title' => 'Production database is down',
            'description' => 'All requests to the production DB are timing out. Ops has been paged. This is a full outage.',
            'priority' => 'critical',
            'category' => 'technical',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.is_escalated', true)
            ->assertJsonPath('data.escalation_reason', 'critical_open');

        $this->assertTrue(Issue::first()->is_escalated);
    }
}
