# Issue Intake and Smart Summary System

Issue Intake and Smart Summary System is a Laravel 12 application for receiving support issues, tracking their status, and generating suggested summaries and next steps.

## What This System Does

- Lets teams submit and manage support issues
- Shows issue status, priority, category, and escalation flags
- Generates a summary and suggested next action for each issue
- Keeps a history of summary attempts (AI and fallback rules)
- Supports both a web UI and JSON API

## Tech Stack

- PHP 8.2+
- Laravel 12
- MySQL (default in `.env.example`)
- Queue driver: `database`
- Vite + Tailwind CSS 4

## Prerequisites

Install these before setup:
- PHP 8.2+
- Composer
- Node.js 22.12+
- npm
- MySQL 8+ (or compatible)

## Quick Setup

```bash
composer run setup
```

This command installs dependencies, creates `.env`, generates app key, runs migrations, and builds frontend assets.

## Configure Environment

Open `.env` and confirm DB + app URL values.

Example:

```env
APP_URL=http://issue-intake.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=issue_intake
DB_USERNAME=root
DB_PASSWORD=
QUEUE_CONNECTION=database
```

## Running the App

### Option A: Laragon (recommended if you use Laragon)

- Start Laragon services (web server + MySQL)
- Open your virtual host URL (example: `http://issue-intake.test`)

Run these background commands in separate terminals:

```bash
php artisan queue:work
```

For frontend changes:

```bash
npm run build
```

## Summarization Configuration

The app supports two summarization drivers:
- `groq` (primary when configured)
- `rules` (fallback)

Set in `.env`:

```env
SUMMARIZER_DRIVER=groq
GROQ_API_KEY=
GROQ_MODEL=llama-3.1-8b-instant
GROQ_TIMEOUT_SECONDS=5
```

Notes:
- If `GROQ_API_KEY` is missing/invalid, app falls back to rules summarization.
- Summary runs asynchronously, so queue worker must be running.

## How to Verify Groq Is Being Used

1. Create an issue or click **Regenerate summary** on an existing issue.
2. Open the issue detail page.
3. Check **Source** in the summary panel:
- `groq · <model>` means AI output
- `rules · rules-v1` means fallback rules output

## API Endpoints

All API routes are under `/api/issues`:
- `GET /api/issues`
- `POST /api/issues`
- `GET /api/issues/{id}`
- `PATCH /api/issues/{id}` (also supports `PUT`)
- `DELETE /api/issues/{id}`
- `POST /api/issues/{id}/restore`
- `POST /api/issues/{id}/regenerate-summary`

## Postman Collection

Import:

- `postman/Issue-Intake-and-Smart-Summary-System-API.postman_collection.json`

Set collection variable `base_url` to your running host (for example `http://issue-intake.test` or `http://localhost:8000`).

## Running Tests

```bash
php artisan test
```

## Project Structure (Quick View)

- `app/Http/Controllers/Web` - web endpoints and views
- `app/Http/Controllers/Api` - API endpoints
- `app/Jobs/GenerateIssueSummary.php` - async summary generation
- `app/Services/Summarization` - Groq + rules summarizers
- `app/Services/Escalation` - escalation evaluator
- `database/migrations` - schema
- `resources/views/issues` - Blade templates

## Troubleshooting

1. Summary stays `pending`:
Run queue worker and ensure `QUEUE_CONNECTION=database` with migrated jobs table (`php artisan migrate`).
2. Groq never appears as source:
Confirm `SUMMARIZER_DRIVER=groq`, set `GROQ_API_KEY`, then run `php artisan config:clear`.
3. UI changes not appearing:
Run `npm run dev` during development, or rebuild with `npm run build`.
