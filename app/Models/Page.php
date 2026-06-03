<?php

namespace App\Models;

use App\Domain\ContentExtraction\Models\PageContent;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Page extends Model
{
    use HasUlids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'website_id',
        'sitemap_id',
        'url',
        'path',
        'parent_path',
        'slug',
        'lastmod',
        'http_status',
        'redirect_url',
        'is_in_sitemap',
    ];

    protected function casts(): array
    {
        return [
            'lastmod' => 'datetime',
            'http_status' => 'integer',
            'is_in_sitemap' => 'boolean',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function sitemap(): BelongsTo
    {
        return $this->belongsTo(Sitemap::class);
    }

    public function pageContents(): HasMany
    {
        return $this->hasMany(PageContent::class);
    }

    public function latestContent(): HasOne
    {
        return $this->hasOne(PageContent::class)->latestOfMany('extracted_at');
    }

    public function aiCredit(): HasOne
    {
        return $this->hasOne(PageAiCredit::class)->latestOfMany('calculated_at');
    }

    public function getContentAttribute(): ?array
    {
        return $this->latestContent?->content;
    }
}
