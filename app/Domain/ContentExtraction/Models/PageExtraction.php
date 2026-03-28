<?php

namespace App\Domain\ContentExtraction\Models;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use App\Domain\ContentExtraction\Enums\PageExtractionStatus;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;

class PageExtraction extends Model
{
    use HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'page_id',
        'content_extraction_run_id', // Must match migration
        'status',
        'error',
        'failure_type',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'status' => PageExtractionStatus::class,
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function run()
    {
        // You MUST specify the foreign key because it doesn't follow 'run_id' convention
        return $this->belongsTo(ContentExtractionRun::class, 'content_extraction_run_id');
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
