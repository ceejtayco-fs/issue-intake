<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_id',
        'driver',
        'model',
        'summary',
        'next_action',
        'status',
        'latency_ms',
        'prompt_tokens',
        'completion_tokens',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'latency_ms' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
        ];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
