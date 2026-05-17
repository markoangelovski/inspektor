<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentExtractionRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $progress = $this->total_pages > 0
            ? round(($this->processed_pages / $this->total_pages) * 100)
            : 0;

        return [
            'id' => $this->id,
            'website_id' => $this->website_id,
            'status' => $this->status->value,
            'total_pages' => $this->total_pages,
            'processed_pages' => $this->processed_pages,
            'progress' => $progress,
            'started_at' => $this->started_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
