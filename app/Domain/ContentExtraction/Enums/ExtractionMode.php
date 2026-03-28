<?php

namespace App\Domain\ContentExtraction\Enums;

enum ExtractionMode: string
{
    case Initial = 'initial';
    case ReRun = 'rerun';
}
