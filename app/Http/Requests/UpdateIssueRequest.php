<?php

namespace App\Http\Requests;

use App\Enums\IssueCategory;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:3', 'max:200'],
            'description' => ['sometimes', 'string', 'min:20', 'max:5000'],
            'priority' => ['sometimes', Rule::enum(IssuePriority::class)],
            'category' => ['sometimes', Rule::enum(IssueCategory::class)],
            'status' => ['sometimes', Rule::enum(IssueStatus::class)],
            'due_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
