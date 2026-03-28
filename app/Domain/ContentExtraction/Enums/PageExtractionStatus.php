<?php

namespace App\Domain\ContentExtraction\Enums;

enum PageExtractionStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';

    case Done = 'done';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Done,
            self::Failed,
            self::Skipped,
        ], true);
    }
}
