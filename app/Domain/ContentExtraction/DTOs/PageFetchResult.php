<?php

namespace App\Domain\ContentExtraction\DTOs;

readonly class PageFetchResult
{
    public function __construct(
        public int $httpStatus,
        public ?string $html,
        public ?string $redirectUrl,
    ) {}

    public function isRedirect(): bool
    {
        return $this->httpStatus >= 300 && $this->httpStatus < 400;
    }
}
