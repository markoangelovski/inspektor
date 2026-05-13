<?php

namespace App\Services;

class AiCreditCalculator
{
    private const SKIP_ATTRS = ['href', 'src', 'rel', 'target', 'srcset', 'loading', 'type', 'hreflang', 'width', 'height'];

    private const HEADING_TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    private const PARAGRAPH_TAGS = ['p', 'blockquote', 'figcaption', 'cite'];

    private const LIST_TAGS = ['li', 'dt', 'dd'];

    private const CELL_TAGS = ['td', 'th'];

    private const UI_TAGS = ['button', 'label'];

    // Two-parameter linear model per language count, fitted to Strapi reference data:
    //   9 words  → 1 lang:  0.09 credits
    //   9 words  → 5 langs: 0.16 credits
    //  60 words  → 5 langs: 0.35 credits (independent example)
    //
    // Language scaling K = 0.16/0.09 = 16/9 ≈ 1.778
    // Paragraph → 1 lang ≈ 0.35 × (9/16) ≈ 0.197 credits (derived)
    //
    // 1-language fit:  A₁ + B₁×9 = 0.09,  A₁ + B₁×60 = 0.197  → B₁=0.002098, A₁=0.0711
    // 5-language fit:  A₅ + B₅×9 = 0.16,  A₅ + B₅×60 = 0.35   → B₅=0.003725, A₅=0.1265
    private const FIXED_ONE = 0.0711;

    private const RATE_ONE = 0.002098;

    private const FIXED_FIVE = 0.1265;

    private const RATE_FIVE = 0.003725;

    public function extractTranslatableSegments(array $content): array
    {
        $segments = [];
        $this->collectHeadSegments($content['head'] ?? [], $segments);
        $this->collectSegments($content['body'] ?? [], $segments, 'text');

        return $segments;
    }

    public function wordCount(array $segments): int
    {
        $allText = implode(' ', array_column($segments, 'text'));
        if ($allText === '') {
            return 0;
        }
        $words = preg_split('/\s+/', trim($allText), -1, PREG_SPLIT_NO_EMPTY);

        return count($words);
    }

    public function creditsOneLanguage(int $wordCount): float
    {
        if ($wordCount === 0) {
            return 0.0;
        }

        return round(self::FIXED_ONE + self::RATE_ONE * $wordCount, 4);
    }

    public function creditsFiveLanguages(int $wordCount): float
    {
        if ($wordCount === 0) {
            return 0.0;
        }

        return round(self::FIXED_FIVE + self::RATE_FIVE * $wordCount, 4);
    }

    private function collectHeadSegments(array $head, array &$segments): void
    {
        $title = is_string($head['title'] ?? null) ? trim($head['title']) : '';
        if ($title !== '') {
            $segments[] = $this->makeSegment('title', $title);
        }

        $meta = $head['meta'] ?? [];

        $description = is_string($meta['description'] ?? null) ? trim($meta['description']) : '';
        if ($description !== '') {
            $segments[] = $this->makeSegment('description', $description);
        }

        $ogTitle = is_string($meta['og:title'] ?? null) ? trim($meta['og:title']) : '';
        if ($ogTitle !== '' && $ogTitle !== $title) {
            $segments[] = $this->makeSegment('title', $ogTitle);
        }

        $ogDescription = is_string($meta['og:description'] ?? null) ? trim($meta['og:description']) : '';
        if ($ogDescription !== '' && $ogDescription !== $description) {
            $segments[] = $this->makeSegment('description', $ogDescription);
        }
    }

    private function collectSegments(mixed $node, array &$segments, string $currentType): void
    {
        if (is_string($node)) {
            if (($trimmed = trim($node)) !== '') {
                $segments[] = $this->makeSegment($currentType, $trimmed);
            }

            return;
        }

        if (! is_array($node)) {
            return;
        }

        foreach ($node as $key => $value) {
            if (is_int($key)) {
                $this->collectSegments($value, $segments, $currentType);

                continue;
            }

            if (in_array($key, self::SKIP_ATTRS, true)) {
                continue;
            }

            $type = $this->tagType($key) ?? $currentType;

            if (is_string($value) && ($trimmed = trim($value)) !== '') {
                $segments[] = $this->makeSegment($type, $trimmed);
            } elseif (is_array($value)) {
                $this->collectSegments($value, $segments, $type);
            }
        }
    }

    private function makeSegment(string $type, string $text): array
    {
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $wc = count($words);

        // Segment credits use the marginal rate only — no fixed overhead per segment.
        return [
            'type' => $type,
            'text' => $text,
            'word_count' => $wc,
            'credits_one' => round($wc * self::RATE_ONE, 4),
            'credits_five' => round($wc * self::RATE_FIVE, 4),
        ];
    }

    private function tagType(string $tag): ?string
    {
        if (in_array($tag, self::HEADING_TAGS, true)) {
            return 'heading';
        }
        if (in_array($tag, self::PARAGRAPH_TAGS, true)) {
            return 'paragraph';
        }
        if (in_array($tag, self::LIST_TAGS, true)) {
            return 'list-item';
        }
        if (in_array($tag, self::CELL_TAGS, true)) {
            return 'cell';
        }
        if (in_array($tag, self::UI_TAGS, true)) {
            return 'ui-text';
        }
        if ($tag === 'alt') {
            return 'alt-text';
        }

        return null;
    }
}
