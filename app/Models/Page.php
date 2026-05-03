<?php

namespace App\Models;

use App\Domain\ContentExtraction\Models\PageContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
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
        'url',
        'path',
        'parent_path',
        'slug',
    ];


    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function pageContents(): HasMany
    {
        return $this->hasMany(PageContent::class);
    }

    public function latestContent(): HasOne
    {
        return $this->hasOne(PageContent::class)->latestOfMany('extracted_at');
    }

    public function getContentAttribute(): ?array
    {
        return $this->latestContent?->content;
    }
}
