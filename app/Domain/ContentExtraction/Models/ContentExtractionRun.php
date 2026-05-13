<?php

namespace App\Domain\ContentExtraction\Models;

use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;
use App\Models\Website;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ContentExtractionRun extends Model
{
    use HasUlids;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'website_id',
        'created_by',
        'status',
        'diff',
        'events',
        'total_pages',
        'processed_pages',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'status' => ContentExtractionRunStatus::class,
        'diff' => 'array',
        'events' => 'array',
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

    public function isInterruptible(): bool
    {
        return in_array(
            $this->status,
            [ContentExtractionRunStatus::Paused],
            true
        );
    }
}
