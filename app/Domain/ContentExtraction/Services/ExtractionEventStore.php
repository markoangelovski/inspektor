<?php

namespace App\Domain\ContentExtraction\Services;

use Illuminate\Support\Facades\Redis;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;

class ExtractionEventStore
{
    private const TTL_SECONDS = 3600; // 1 hour

    public function append(ContentExtractionRun $run, array $event): void
    {
        $key = $this->key($run->id);

        Redis::rpush($key, json_encode([
            'id'        => (string) str()->ulid(),
            'run_id'    => $run->id,
            'website_id' => $run->website_id,
            'timestamp' => now()->toISOString(),
            ...$event,
        ]));

        Redis::expire($key, self::TTL_SECONDS);
    }

    public function all(ContentExtractionRun $run): array
    {
        $key = $this->key($run->id);

        return array_map(
            fn($e) => json_decode($e, true),
            Redis::lrange($key, 0, -1)
        );
    }

    public function pull(ContentExtractionRun $run): array
    {
        $events = $this->all($run);
        Redis::del($this->key($run->id));
        return $events;
    }

    private function key(string $runId): string
    {
        return "content-extraction:run:{$runId}:events";
    }
}
