<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'website_id' => $this->website_id,
            'url' => $this->url,
            'path' => $this->path,
            'slug' => $this->slug,
            'lastmod' => $this->lastmod?->toIso8601String(),
            'has_content' => $this->whenLoaded('latestContent', fn () => $this->latestContent !== null),
            'content' => $this->whenLoaded('latestContent', fn () => new PageContentResource($this->latestContent)),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
