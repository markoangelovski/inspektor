<?php

namespace App\Domain\ContentExtraction\Models;

use App\Domain\ContentExtraction\Enums\PageExtractionStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class PageExtraction extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'page_id',
        'website_id',
        'content_extraction_run_id',
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
