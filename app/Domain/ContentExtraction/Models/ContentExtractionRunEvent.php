<?php

namespace App\Domain\ContentExtraction\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class ContentExtractionRunEvent extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'website_id',
        'content_extraction_run_id',
        'type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
