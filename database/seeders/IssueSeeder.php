<?php

namespace Database\Seeders;

use App\Enums\IssueCategory;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\SummaryStatus;
use App\Models\Issue;
use App\Models\IssueSummary;
use Illuminate\Database\Seeder;

class IssueSeeder extends Seeder
{
    public function run(): void
    {
        $handcrafted = [
            [
                'title' => 'Duplicate annual subscription charge after renewal',
                'description' => 'Finance contact reports that invoice INV-2026-0412 was paid automatically on April 12, but a second card charge for the same amount posted on April 13. Their card statement shows two settled transactions for $1,199.00. They need the duplicate refunded before monthly close.',
                'priority' => IssuePriority::High,
                'category' => IssueCategory::Billing,
                'summary' => 'Customer was charged twice for a single annual renewal and requests a duplicate refund.',
                'next_action' => 'Confirm duplicate payment IDs in billing gateway, refund the duplicate charge, and share refund confirmation ETA.',
            ],
            [
                'title' => 'SAML SSO login loop for entire EU tenant',
                'description' => 'Users in the Acme EU tenant are redirected between the identity provider and our login callback without reaching the dashboard. The issue started immediately after IdP metadata rotation at 08:40 UTC. More than 120 users are blocked from signing in.',
                'priority' => IssuePriority::Critical,
                'category' => IssueCategory::Access,
                'summary' => 'Tenant-wide SAML SSO loop is preventing users from logging in after IdP metadata rotation.',
                'next_action' => 'Validate SAML certificate and ACS settings against new IdP metadata, then deploy a hotfix and notify impacted tenant admins.',
            ],
            [
                'title' => 'Webhook signature verification failing after certificate update',
                'description' => 'All incoming payment webhooks started failing signature validation after last night\'s certificate rotation. Merchant events are queued but not processed, causing delayed order fulfillment and failed reconciliation jobs in downstream systems.',
                'priority' => IssuePriority::High,
                'category' => IssueCategory::Technical,
                'summary' => 'Payment webhooks are failing signature checks after certificate rotation, delaying order processing.',
                'next_action' => 'Compare active signing keys and webhook secret configuration, replay failed events in staging, then reprocess backlog once validated.',
            ],
            [
                'title' => 'Cannot enroll MFA after company phone migration',
                'description' => 'Several employees moved to new company-managed phones and now cannot complete MFA enrollment in the authenticator app. They can enter credentials but are blocked on second factor setup. IT confirmed device time sync is correct.',
                'priority' => IssuePriority::High,
                'category' => IssueCategory::Access,
                'summary' => 'Users with newly issued phones are blocked from MFA enrollment and cannot finish login.',
                'next_action' => 'Review MFA enrollment logs for device binding failures, provide temporary recovery codes, and reset authenticator enrollment for affected users.',
            ],
            [
                'title' => 'Production API /api/orders returning 500 for all requests',
                'description' => 'Since 09:15 UTC the /api/orders endpoint has returned HTTP 500 for every request from integration partners. Error rate is currently 100%, and partner systems cannot submit new orders. Operations declared a Sev-1 incident.',
                'priority' => IssuePriority::Critical,
                'category' => IssueCategory::Technical,
                'summary' => 'Sev-1 outage on /api/orders is blocking all partner order submissions.',
                'next_action' => 'Page on-call, inspect recent deploy and database error logs, roll back if needed, and post incident updates every 15 minutes.',
            ],
            [
                'title' => 'Data portability request for full account export',
                'description' => 'Customer legal team submitted a data portability request and asked for complete account export in machine-readable format, including audit logs, attachments, and user activity history. They requested delivery within seven days.',
                'priority' => IssuePriority::Medium,
                'category' => IssueCategory::Account,
                'summary' => 'Customer requested a full account data export for compliance and portability.',
                'next_action' => 'Verify requester authorization, generate signed export package, and deliver via secure link with expiration policy.',
            ],
            [
                'title' => 'Invoice tax breakdown does not match downloadable PDF',
                'description' => 'Billing admin reports that VAT values shown on the invoice details page do not match the generated PDF for the same invoice. The web view shows 12% VAT while PDF shows 0% for transactions in the same period.',
                'priority' => IssuePriority::High,
                'category' => IssueCategory::Billing,
                'summary' => 'VAT values differ between invoice UI and generated PDF for identical records.',
                'next_action' => 'Compare tax computation paths for UI and PDF renderer, identify mismatch in jurisdiction rules, and regenerate affected invoices.',
            ],
            [
                'title' => 'Role permissions lost after team merge',
                'description' => 'After consolidating two departments into one team, project managers lost permission to approve budget requests. Users still appear in the correct team but actions return 403 Forbidden. This affects time-sensitive approvals.',
                'priority' => IssuePriority::High,
                'category' => IssueCategory::Account,
                'summary' => 'Team merge changed effective role permissions, causing 403 errors on approval actions.',
                'next_action' => 'Audit permission inheritance for merged team IDs, restore manager approval scope, and backfill missing role assignments.',
            ],
            [
                'title' => 'Mobile app crashes when attaching receipt photos on iOS',
                'description' => 'Users on iOS 18 report that the app crashes when attaching more than three receipt photos to an expense ticket. Crash happens consistently after camera permission prompt is accepted. Android users are not affected.',
                'priority' => IssuePriority::Medium,
                'category' => IssueCategory::Technical,
                'summary' => 'iOS app crashes when users attach multiple receipt photos to an expense ticket.',
                'next_action' => 'Reproduce on iOS 18, capture crash logs, isolate memory issue in image upload flow, and prepare a patch release.',
            ],
            [
                'title' => 'Feature request: bulk close stale low-priority tickets',
                'description' => 'Support leads requested a bulk action to close low-priority tickets that have had no activity for over 60 days. Current process is manual and takes several hours weekly.',
                'priority' => IssuePriority::Low,
                'category' => IssueCategory::Feedback,
                'summary' => 'Support team requested bulk closing of stale low-priority tickets to reduce manual workload.',
                'next_action' => 'Add feature to backlog with UX proposal and guardrails, then evaluate impact with support operations.',
            ],
            [
                'title' => 'Unable to access admin panel after RBAC policy rollout',
                'description' => 'Multiple administrators are now redirected to 403 when opening /admin after yesterday\'s RBAC policy deployment. Incident impacts user provisioning and billing override approvals. Affected accounts previously had full admin rights.',
                'priority' => IssuePriority::Critical,
                'category' => IssueCategory::Access,
                'summary' => 'RBAC rollout locked out administrators from /admin and blocked operational workflows.',
                'next_action' => 'Review RBAC migration changes, restore admin policy mappings, and perform targeted access validation for impacted users.',
            ],
        ];

        foreach ($handcrafted as $row) {
            $issue = Issue::factory()->create([
                'title' => $row['title'],
                'description' => $row['description'],
                'priority' => $row['priority'],
                'category' => $row['category'],
                'status' => IssueStatus::Open,
                'summary_status' => SummaryStatus::Ready,
                'summary' => $row['summary'],
                'next_action' => $row['next_action'],
            ]);

            IssueSummary::create([
                'issue_id' => $issue->id,
                'driver' => 'rules',
                'model' => 'rules-v1',
                'summary' => $row['summary'],
                'next_action' => $row['next_action'],
                'status' => 'succeeded',
                'latency_ms' => rand(5, 40),
            ]);
        }

        Issue::factory()->count(8)->withSummary()->create()->each(function (Issue $issue) {
            IssueSummary::create([
                'issue_id' => $issue->id,
                'driver' => 'rules',
                'model' => 'rules-v1',
                'summary' => $issue->summary,
                'next_action' => $issue->next_action,
                'status' => 'succeeded',
                'latency_ms' => rand(5, 40),
            ]);
        });

        Issue::factory()->count(3)->escalated()->withSummary()->create()->each(function (Issue $issue) {
            IssueSummary::create([
                'issue_id' => $issue->id,
                'driver' => 'rules',
                'model' => 'rules-v1',
                'summary' => $issue->summary,
                'next_action' => $issue->next_action,
                'status' => 'succeeded',
                'latency_ms' => rand(5, 40),
            ]);
        });

        Issue::factory()->count(4)->create([
            'summary_status' => SummaryStatus::Pending,
        ]);

        Issue::factory()->count(4)->create([
            'status' => IssueStatus::Resolved,
            'summary_status' => SummaryStatus::Ready,
            'summary' => 'Resolved.',
            'next_action' => 'No further action required.',
        ])->each(function (Issue $issue) {
            IssueSummary::create([
                'issue_id' => $issue->id,
                'driver' => 'rules',
                'model' => 'rules-v1',
                'summary' => 'Resolved.',
                'next_action' => 'No further action required.',
                'status' => 'succeeded',
                'latency_ms' => rand(5, 20),
            ]);
        });
    }
}
