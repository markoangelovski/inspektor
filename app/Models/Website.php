<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;

class Website extends Model
{
    use HasUlids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'type',

        'meta_title',
        'meta_description',
        'meta_image_url',
        'metadata_processed',

        'sitemaps_fetched',
        'sitemaps_count',
        'sitemaps_last_sync',
        'sitemaps_message',
        'sitemaps_processing',

        'pages_fetched',
        'pages_count',
        'pages_last_sync',
        'pages_message',
        'pages_processing',
    ];

    protected $casts = [
        'metadata_processed' => 'boolean',

        'sitemaps_fetched' => 'boolean',
        'sitemaps_count' => 'integer',
        'sitemaps_last_sync' => 'datetime',

        'pages_fetched' => 'boolean',
        'pages_count' => 'integer',
        'pages_last_sync' => 'datetime',
    ];

    /**
     * A website has many sitemaps
     */
    public function sitemaps(): HasMany
    {
        return $this->hasMany(Sitemap::class);
    }

    /**
     * A website has many pages
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function latestRun()
    {
        return $this->hasOne(ContentExtractionRun::class)->latestOfMany();
    }
}
