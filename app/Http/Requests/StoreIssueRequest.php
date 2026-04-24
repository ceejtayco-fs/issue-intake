<?php

namespace App\Http\Requests;

use App\Enums\IssueCategory;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'priority' => ['required', Rule::enum(IssuePriority::class)],
            'category' => ['required', Rule::enum(IssueCategory::class)],
            'status' => ['sometimes', Rule::enum(IssueStatus::class)],
            'due_at' => ['sometimes', 'nullable', 'date', 'after:now'],
        ];
    }
}
