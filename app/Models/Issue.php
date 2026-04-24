<?php

namespace App\Models;

use App\Enums\IssueCategory;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\SummaryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Issue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'category',
        'status',
        'summary',
        'next_action',
        'summary_status',
        'is_escalated',
        'escalated_at',
        'escalation_reason',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => IssuePriority::class,
            'category' => IssueCategory::class,
            'status' => IssueStatus::class,
            'summary_status' => SummaryStatus::class,
            'is_escalated' => 'boolean',
            'escalated_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(IssueSummary::class);
    }

    public function latestSummary(): HasOne
    {
        return $this->hasOne(IssueSummary::class)->latestOfMany();
    }

    public function latestSuccessfulSummary(): HasOne
    {
        return $this->hasOne(IssueSummary::class)
            ->where('status', 'succeeded')
            ->latestOfMany();
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when(
                array_key_exists('is_escalated', $filters) && $filters['is_escalated'] !== null,
                fn ($q) => $q->where('is_escalated', filter_var($filters['is_escalated'], FILTER_VALIDATE_BOOLEAN))
            )
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            });
    }
}
