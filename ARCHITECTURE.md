# Architecture

## Short Overview

Issue Intake and Smart Summary System is a Laravel monolith with one shared backend serving both:
- Web UI (for support teams)
- JSON API (for integrations and testing tools)

The core object is an `Issue`. Each issue can be summarized automatically and flagged for escalation based on business rules.

## Key Decisions

1. Monolith architecture
Reason: fastest to build and maintain for a small-to-mid product scope, with fewer deployment and coordination overheads.

2. Asynchronous summary generation
Reason: issue creation/update should stay fast for users; summary generation runs in background via queue.

3. Groq-first with rules fallback
Reason: use AI quality when available, but guarantee continuity when API/key/network fails.

4. Escalation checks in two paths
Reason: immediate checks on create/update catch urgent items quickly; scheduled checks catch time-based escalation conditions.

5. Summary attempt history is stored
Reason: provides transparency, troubleshooting, and auditability of source (`groq` vs `rules`) and failures.

## End-to-End Flow

1. User submits or updates an issue.
2. System validates input and saves issue state.
3. Escalation rules are evaluated immediately.
4. Summary job is queued.
5. Worker processes summary:
- tries configured primary driver
- falls back to rules if needed
6. Latest summary/next action is written to `issues`.
7. Attempt details are recorded in `issue_summaries`.

## Data Model (High Level)

- `issues`
Holds current issue state (title, description, priority, status, summary fields, escalation fields, due date, soft delete).

- `issue_summaries`
Holds per-attempt summary history (driver/model, output, status, latency/tokens, error).

## Operational Requirements

- Queue worker must run for summary generation.
- Scheduler/cron must run for periodic escalation evaluation.
- If Groq is not configured or fails, system continues with rules summarizer.

## Technical Appendix (Brief)

- Web routes: `routes/web.php`
- API routes: `routes/api.php`
- Scheduler: `routes/console.php`
- Web controller: `App\Http\Controllers\Web\IssueWebController`
- API controller: `App\Http\Controllers\Api\IssueController`
- Summary job: `App\Jobs\GenerateIssueSummary`
- Summarization services: `App\Services\Summarization\*`
- Escalation evaluator: `App\Services\Escalation\EscalationEvaluator`

Config keys:
- `SUMMARIZER_DRIVER`
- `GROQ_API_KEY`
- `GROQ_MODEL`
- `GROQ_TIMEOUT_SECONDS`
- `GROQ_ENDPOINT`

## If I Had More Time

- I would make reliability more visible day to day by adding stronger monitoring, clearer logs, and alerts so failures are noticed early instead of discovered late.
- I would improve overall user experience by polishing workflows (especially filtering and issue handling), and expanding test coverage to protect against regressions.
- I would harden the system for production by doing deeper performance tuning, running load tests, and documenting a more complete operations/deployment playbook.
