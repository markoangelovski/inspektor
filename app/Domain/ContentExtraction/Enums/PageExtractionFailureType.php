<?php

namespace App\Domain\ContentExtraction\Enums;

enum PageExtractionFailureType: string
{
    case Network = 'network'; // DNS, Connection Refused, Timeouts
    case RateLimited = 'rate_limited'; // 429 Too Many Requests
    case ParseTemporary = 'parse_temporary'; // 500, 502, 503, 504
    case ParsePermanent = 'parse_permanent'; // 404 Not Found, 410 Gone, 403 Forbidden
    case Cancelled = 'cancelled';
    case Unknown = 'unknown';

    public function isRetryable(): bool
    {
        return in_array($this, [
            self::Network,
            self::RateLimited,
            self::ParseTemporary,
        ], true);
    }

    /**
     * Map HTTP status codes to extraction failure types.
     */
    public static function fromStatusCode(?int $code): self
    {
        if (!$code) {
            return self::Unknown;
        }

        return match (true) {
            $code === 404 || $code === 410 => self::ParsePermanent, // Page is explicitly missing or gone
            $code === 403 => self::ParsePermanent, // Forbidden - often a permanent block or geo-fence
            $code === 429 => self::RateLimited,    // Rate limited by the target server
            $code >= 500 => self::ParseTemporary,  // Server-side errors (retryable)
            $code >= 400 && $code < 500 => self::ParsePermanent, // Other client errors
            default => self::Unknown,
        };
    }
}
