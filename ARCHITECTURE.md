# Architecture

## Plain-Language Overview

This system helps a support team do three things:
1. Capture issues from users.
2. Track issue progress (open, in progress, resolved, closed).
3. Suggest a short summary and a recommended next step.

Think of it as a ticket tracker with a built-in assistant.

## Main Parts (Non-Technical)

- Web app: used by support staff in the browser.
- API: used by other systems (or Postman) to do the same actions.
- Database: stores issues and summary history.
- Queue worker: runs summary generation in the background.
- Scheduler: periodically checks if issues should be escalated.

## How an Issue Moves Through the System

### 1) New issue is submitted

- User creates an issue (title, description, priority, category).
- System saves it immediately.
- System checks if issue should be escalated right away.
- System queues summary generation in the background.

Result: issue appears quickly, while summary is prepared asynchronously.

### 2) Summary is generated

- System tries the configured primary summarizer first.
- If Groq is configured and available, it uses Groq.
- If Groq is unavailable or fails, system falls back to rule-based summarization.
- Final summary and suggested next action are saved on the issue.
- Every attempt is recorded in **Summary history**.

Result: there is always a best-effort summary, plus a history trail for transparency.

### 3) Escalation is applied

Escalation can happen:
- Immediately during create/update operations.
- Every 15 minutes via scheduled background check.

Current escalation rules:
- Critical + Open issue
- High/Critical issue still active after 24 hours
- Overdue issue not yet resolved/closed

Result: urgent issues are visibly flagged for faster action.

## What "Summary History" Means

Summary history is the timeline of summary attempts for one issue.

Each history row shows:
- Source (`driver`) such as `groq` or `rules`
- Model used
- Success or failure
- Timing and token metrics (when available)
- Error details if a run failed

This helps teams answer:
- "Did AI run, or did fallback rules run?"
- "Why did this summary look this way?"
- "Did a previous attempt fail?"

## Data Stored

### `issues` table (current state)

Stores the latest state of each issue, including:
- Core fields: title, description, priority, category, status
- Current summary fields: summary, next_action, summary_status
- Escalation fields: is_escalated, escalated_at, escalation_reason
- Due date and soft-delete timestamp

### `issue_summaries` table (history)

Stores each summary attempt with:
- Driver/model used
- Output or failure
- Basic run metrics

## Reliability Design

- Summary jobs retry automatically (3 attempts with backoff).
- If primary summarizer fails, system falls back to rules.
- Failures are logged and persisted.
- If all retries/fallback fail, summary status is marked `failed`.

## Technical Appendix (For Developers)

### Key Files

- Routing
- `routes/web.php`
- `routes/api.php`
- `routes/console.php`

- Controllers
- `App\Http\Controllers\Web\IssueWebController`
- `App\Http\Controllers\Api\IssueController`

- Validation
- `App\Http\Requests\StoreIssueRequest`
- `App\Http\Requests\UpdateIssueRequest`

- Async and Services
- `App\Jobs\GenerateIssueSummary`
- `App\Services\Summarization\SummaryService`
- `App\Services\Summarization\GroqSummarizer`
- `App\Services\Summarization\RulesSummarizer`
- `App\Services\Escalation\EscalationEvaluator`

### Config Keys

- `SUMMARIZER_DRIVER`
- `GROQ_API_KEY`
- `GROQ_MODEL`
- `GROQ_TIMEOUT_SECONDS`
- `GROQ_ENDPOINT`

### Contributor Notes

When changing business behavior:
1. Keep web and API behavior aligned unless intentionally different.
2. Update FormRequest validation when input rules change.
3. Preserve async summary behavior (avoid blocking request thread).
4. Update tests for changed behavior.
5. If output shape changes, update both API resource and Blade views.
