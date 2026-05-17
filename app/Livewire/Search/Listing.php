<?php

namespace App\Livewire\Search;

use App\Domain\ContentExtraction\Models\PageContent;
use Livewire\Component;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination;

    public string $query = '';

    public string $reindexStatus = '';

    public bool $reindexError = false;

    public function reindex(): void
    {
        $this->reindexStatus = '';
        $this->reindexError = false;

        set_time_limit(300);

        try {
            $client = app(\Meilisearch\Client::class);
            $index = $client->index((new PageContent)->searchableAs());
            $task = $index->deleteAllDocuments();
            $client->waitForTask($task['taskUid']);

            PageContent::makeAllSearchable();

            $count = PageContent::count();
            $this->reindexStatus = "Sent {$count} records to Meilisearch.";
        } catch (\Throwable $e) {
            $this->reindexError = true;
            $this->reindexStatus = $e->getMessage();
        }
    }

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $results = null;

        if (strlen($this->query) >= 2) {
            $results = PageContent::search($this->query)
                ->options(['attributesToHighlight' => ['*']])
                ->query(fn ($q) => $q->with('page'))
                ->paginate(10)
                ->through(fn ($result) => $this->processResult($result));
        }

        return view('livewire.search.listing', ['results' => $results]);
    }

    private function processResult(PageContent $result): array
    {
        $head = $result->content['head'] ?? [];
        $formatted = $result->scoutMetadata()['_formatted'] ?? [];

        $title = $formatted['title'] ?? $head['title'] ?? $head['meta']['og:title'] ?? null;
        $description = $formatted['description'] ?? $head['meta']['description'] ?? $head['meta']['og:description'] ?? null;
        $bodyText = $formatted['body_text'] ?? null;

        $canonical = $head['canonical'] ?? $head['meta']['og:url'] ?? null;
        $canonicalPath = $canonical ? (parse_url($canonical, PHP_URL_PATH) ?? $canonical) : null;

        return [
            'title' => $this->sanitize($title ? strip_tags($title, '<em>') : null),
            'description' => $this->sanitize($description ? strip_tags($description, '<em>') : null),
            'canonical' => $this->sanitize($canonical),
            'canonicalPath' => $this->sanitize($canonicalPath),
            'bodySnippet' => $this->sanitize(
                $bodyText && str_contains($bodyText, '<em>') ? $this->excerptAround($bodyText) : null
            ),
            'path' => $this->sanitize($result->page?->path),
            'websiteId' => $result->page?->website_id,
        ];
    }

    // Extracts a window of text centred on the first Meilisearch highlight marker.
    // Uses mb_* functions so multi-byte UTF-8 sequences are never split mid-character.
    private function excerptAround(string $text, int $window = 250): string
    {
        $emPos = mb_strpos($text, '<em>');
        $start = $emPos !== false ? max(0, $emPos - 100) : 0;
        $excerpt = mb_substr($text, $start, $window + 100);

        return ($start > 0 ? '…' : '').strip_tags($excerpt, '<em>');
    }

    private function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Replace any invalid UTF-8 byte sequences so JSON encoding never fails.
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
