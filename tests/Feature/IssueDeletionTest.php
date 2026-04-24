<?php

namespace Tests\Feature;

use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_soft_deletes_and_excludes_from_default_list(): void
    {
        $issue = Issue::factory()->create();

        $this->deleteJson("/api/issues/{$issue->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('issues', ['id' => $issue->id]);

        $this->getJson("/api/issues/{$issue->id}")
            ->assertNotFound();
    }

    public function test_restore_recovers_a_soft_deleted_issue(): void
    {
        $issue = Issue::factory()->create();
        $issue->delete();

        $this->postJson("/api/issues/{$issue->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.id', $issue->id)
            ->assertJsonPath('data.deleted_at', null);

        $this->assertNotSoftDeleted('issues', ['id' => $issue->id]);
    }

    public function test_web_show_can_view_soft_deleted_issue_and_offer_restore(): void
    {
        $issue = Issue::factory()->create();
        $issue->delete();

        $this->get("/issues/{$issue->id}")
            ->assertOk()
            ->assertSee('Restore');
    }
}
