<?php

namespace App\Enums;

enum IssueStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function isActive(): bool
    {
        return $this === self::Open || $this === self::InProgress;
    }
}
