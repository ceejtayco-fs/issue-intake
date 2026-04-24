<?php

namespace App\Services\Escalation;

final readonly class EscalationDecision
{
    public function __construct(
        public bool $shouldEscalate,
        public ?string $reason = null,
    ) {
    }

    public static function none(): self
    {
        return new self(shouldEscalate: false);
    }

    public static function because(string $reason): self
    {
        return new self(shouldEscalate: true, reason: $reason);
    }
}
