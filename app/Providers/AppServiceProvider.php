<?php

namespace App\Providers;

use App\Services\Summarization\GroqSummarizer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GroqSummarizer::class, function ($app) {
            $config = $app['config']->get('summarization.groq');

            return new GroqSummarizer(
                apiKey: (string) ($config['api_key'] ?? ''),
                model: (string) ($config['model'] ?? 'llama-3.1-8b-instant'),
                timeoutSeconds: (int) ($config['timeout'] ?? 5),
                endpoint: (string) ($config['endpoint'] ?? 'https://api.groq.com/openai/v1/chat/completions'),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
