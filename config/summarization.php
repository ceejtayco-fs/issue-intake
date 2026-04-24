<?php

return [
    'driver' => env('SUMMARIZER_DRIVER', 'groq'),

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
        'timeout' => (int) env('GROQ_TIMEOUT_SECONDS', 5),
        'endpoint' => env('GROQ_ENDPOINT', 'https://api.groq.com/openai/v1/chat/completions'),
    ],
];
