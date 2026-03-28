<?php

namespace App\Domain\ContentExtraction\Models;

use App\Models\Website;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use App\Domain\ContentExtraction\Enums\ExtractionMode;
use App\Domain\ContentExtraction\Models\PageExtraction;
use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;

class ContentExtractionRun extends Model
{
    use HasUlids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'website_id',        // ✅ allow mass assignment
        'status',            // current run status
        'mode',              // initial / rerun
        'extractor_version', // e.g. readability-v1
        'config',            // optional config JSON
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'status' => ContentExtractionRunStatus::class,
        'mode' => ExtractionMode::class,
        'config' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function pageExtractions()
    {
        return $this->hasMany(PageExtraction::class, 'content_extraction_run_id', 'id');
    }

    public function incrementProcessed(): void
    {
        $this->increment('processed_pages');
    }

    public function isAllProcessed(): bool
    {
        $this->refresh();
        return $this->processed_pages >= $this->total_pages;
    }

    // Add a helper to check if we should continue working
    public function isInterruptible(): bool
    {
        return in_array(
            $this->status,
            [ContentExtractionRunStatus::Paused, ContentExtractionRunStatus::Cancelling],
            true
        );
    }
}
