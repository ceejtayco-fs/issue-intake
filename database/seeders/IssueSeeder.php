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
                'title' => 'Charged twice for March invoice',
                'description' => 'Customer reports that their March subscription was charged twice on the 3rd. They see two identical charges of $49.00 on their credit card statement. They want a refund for the duplicate charge.',
                'priority' => IssuePriority::High,
                'category' => IssueCategory::Billing,
                'summary' => 'Customer charged twice for March subscription and is requesting a refund.',
                'next_action' => 'Verify duplicate charge in Stripe and issue refund for the second transaction.',
            ],
            [
                'title' => 'Cannot reset password',
                'description' => 'User has tried to reset their password three times using the forgot-password link but the reset email never arrives. They have checked spam folder and confirmed the email address is correct.',
                'priority' => IssuePriority::Medium,
                'category' => IssueCategory::Access,
                'summary' => 'Password reset emails are not being delivered to a specific user.',
                'next_action' => 'Check mail logs for the user\'s email and verify their address is not bounce-listed.',
            ],
            [
                'title' => 'Production API returning 500 errors',
                'description' => 'Since 09:15 UTC the /api/orders endpoint has been returning HTTP 500 for every request. Error rate is 100%. Ops team has been paged. This is blocking all order processing for integration partners.',
                'priority' => IssuePriority::Critical,
                'category' => IssueCategory::Technical,
                'summary' => 'Full outage on orders API since 09:15 UTC, 100% error rate.',
                'next_action' => 'Page on-call engineer, check recent deploys, roll back if needed.',
            ],
            [
                'title' => 'Request to export all my data',
                'description' => 'I would like to receive a full export of my account data in CSV or JSON format as per the data portability policy. Please include all messages, files, and account settings.',
                'priority' => IssuePriority::Low,
                'category' => IssueCategory::Account,
                'summary' => 'User requesting a full data export for portability.',
                'next_action' => 'Generate export via admin tool and send secure download link to verified email.',
            ],
            [
                'title' => 'Feature: dark mode for the dashboard',
                'description' => 'It would be great to have a dark mode option on the dashboard. I work late and the white background strains my eyes. Many competing products already offer this.',
                'priority' => IssuePriority::Low,
                'category' => IssueCategory::Feedback,
                'summary' => 'User requesting dark mode support on the main dashboard.',
                'next_action' => 'Add to product backlog and triage during next planning session.',
            ],
            [
                'title' => 'Cannot access admin panel',
                'description' => 'I am an admin but when I visit /admin I am redirected to a 403 Forbidden page. This started happening after the deploy yesterday. Other admins on my team report the same issue.',
                'priority' => IssuePriority::High,
                'category' => IssueCategory::Access,
                'summary' => 'Multiple admins locked out of the admin panel since yesterday\'s deploy.',
                'next_action' => 'Review RBAC migration in yesterday\'s release and restore admin role claims.',
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
