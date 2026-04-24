<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Issue
 */
class IssueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority->value,
            'category' => $this->category->value,
            'status' => $this->status->value,
            'summary' => $this->summary,
            'next_action' => $this->next_action,
            'summary_status' => $this->summary_status->value,
            'is_escalated' => (bool) $this->is_escalated,
            'escalated_at' => $this->escalated_at?->toIso8601String(),
            'escalation_reason' => $this->escalation_reason,
            'due_at' => $this->due_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
