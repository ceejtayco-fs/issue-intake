<?php

namespace App\Services\Summarization;

use App\Models\Issue;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqSummarizer implements SummarizerInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $timeoutSeconds,
        private readonly string $endpoint,
    ) {
    }

    public function summarize(Issue $issue): SummaryResult
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('GROQ_API_KEY is not configured.');
        }

        $prompt = $this->buildPrompt($issue);

        $start = microtime(true);

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeoutSeconds)
            ->acceptJson()
            ->asJson()
            ->post($this->endpoint, [
                'model' => $this->model,
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a support operations triage assistant. Given a ticket, return JSON with keys "summary" and "next_action". The summary is one sentence (max 280 chars) describing the issue. The next_action is one actionable sentence (max 280 chars) for the support agent. Return ONLY JSON, no prose.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        $latency = (int) round((microtime(true) - $start) * 1000);

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf('Groq API returned HTTP %d: %s', $response->status(), $response->body())
            );
        }

        $payload = $response->json();
        $content = data_get($payload, 'choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('Groq response missing message content.');
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded) || empty($decoded['summary']) || empty($decoded['next_action'])) {
            throw new RuntimeException('Groq response was not valid JSON with summary and next_action.');
        }

        return new SummaryResult(
            summary: $this->truncate((string) $decoded['summary'], 500),
            nextAction: $this->truncate((string) $decoded['next_action'], 500),
            driver: $this->name(),
            model: $this->model,
            latencyMs: $latency,
            promptTokens: data_get($payload, 'usage.prompt_tokens'),
            completionTokens: data_get($payload, 'usage.completion_tokens'),
        );
    }

    public function name(): string
    {
        return 'groq';
    }

    public function model(): ?string
    {
        return $this->model;
    }

    private function buildPrompt(Issue $issue): string
    {
        return sprintf(
            "Title: %s\nPriority: %s\nCategory: %s\n\nDescription:\n%s",
            $issue->title,
            $issue->priority->value,
            $issue->category->value,
            $issue->description,
        );
    }

    private function truncate(string $text, int $limit): string
    {
        return mb_strlen($text) <= $limit ? $text : mb_substr($text, 0, $limit - 1) . '…';
    }
}
