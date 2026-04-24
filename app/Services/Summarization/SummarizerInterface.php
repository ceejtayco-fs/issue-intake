<?php

namespace App\Services\Summarization;

use App\Models\Issue;

interface SummarizerInterface
{
    public function summarize(Issue $issue): SummaryResult;

    public function name(): string;

    public function model(): ?string;
}
