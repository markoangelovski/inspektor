<?php

namespace App\Domain\ContentExtraction\Models;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class PageContent extends Model
{
    use HasUlids;
    use Searchable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'page_id',
        'content',
        'extracted_at',
    ];

    protected $casts = [
        'content' => 'array',
        'extracted_at' => 'datetime',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('page');
    }

    public function toSearchableArray(): array
    {
        $head = $this->content['head'] ?? [];

        return [
            'path' => $this->page?->path ?? '',
            'title' => $head['title'] ?? $head['meta']['og:title'] ?? '',
            'description' => $head['meta']['description'] ?? $head['meta']['og:description'] ?? '',
            'canonical' => $head['canonical'] ?? $head['meta']['og:url'] ?? '',
            'body_text' => $this->flattenBodyText($this->content['body'] ?? []),
        ];
    }

    private function flattenBodyText(array $node): string
    {
        $texts = [];
        $this->collectBodyTexts($node, $texts);

        return implode(' ', $texts);
    }

    private function collectBodyTexts(mixed $node, array &$texts): void
    {
        // Plain string reached via numeric array or direct element value
        if (is_string($node)) {
            if (($trimmed = trim($node)) !== '') {
                $texts[] = $trimmed;
            }

            return;
        }

        if (! is_array($node)) {
            return;
        }

        foreach ($node as $key => $value) {
            if (is_int($key)) {
                // Numeric array item — string content or child node
                $this->collectBodyTexts($value, $texts);
            } elseif (in_array($key, ['href', 'src', 'rel', 'target', 'srcset', 'loading', 'type', 'hreflang', 'width', 'height'], true)) {
                // HTML attribute — not readable text
                continue;
            } elseif (is_string($value) && ($trimmed = trim($value)) !== '') {
                // Named element with direct text content: "h1": "Heading", "p": "text", "_text": "…"
                $texts[] = $trimmed;
            } elseif (is_array($value)) {
                $this->collectBodyTexts($value, $texts);
            }
        }
    }
}
