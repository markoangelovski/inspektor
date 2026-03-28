<?php

namespace App\Domain\ContentExtraction\Enums;

enum ContentExtractionRunStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Paused = 'paused';

    case Completed = 'completed';
    case CompletedWithErrors = 'completed_with_errors';
    case Failed = 'failed';

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Completed,
            self::CompletedWithErrors,
            self::Failed,
        ], true);
    }

    public function isPaused(): bool
    {
        return $this === self::Paused;
    }
}
