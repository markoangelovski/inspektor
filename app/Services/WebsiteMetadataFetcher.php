<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use DOMDocument;
use DOMXPath;

class WebsiteMetadataFetcher
{
    public function fetch(string $url): ?array
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; WebsiteMonitor/1.0)',
            ])
            ->get($url);

        if (! $response->successful()) {
            return null;
        }

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($response->body());

        $xpath = new DOMXPath($dom);

        return [
            'title' => $this->getMetaContent($xpath, 'og:title')
                ?? $this->getTitleTag($dom),

            'description' => $this->getMetaContent($xpath, 'og:description')
                ?? $this->getMetaContent($xpath, 'description'),

            'image' => $this->resolveImageUrl(
                $url,
                $this->getMetaContent($xpath, 'og:image')
            ),
        ];
    }

    protected function getMetaContent(DOMXPath $xpath, string $name): ?string
    {
        $node = $xpath
            ->query("//meta[@property='$name' or @name='$name']/@content")
            ->item(0);

        return $node ? trim($node->nodeValue) : null;
    }

    protected function getTitleTag(DOMDocument $dom): ?string
    {
        $titles = $dom->getElementsByTagName('title');

        return $titles->length
            ? trim($titles->item(0)->textContent)
            : null;
    }

    protected function resolveImageUrl(string $baseUrl, ?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($image, '/');
    }
}
