<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'type' => $this->type,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_image_url' => $this->meta_image_url,
            'pages_count' => $this->pages_count,
            'pages_last_sync' => $this->pages_last_sync?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
