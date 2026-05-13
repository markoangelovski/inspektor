<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sitemap extends Model
{
    use HasUlids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'url',
        'lastmod',
        'website_id',
    ];

    protected $casts = [
        'lastmod' => 'datetime',
    ];

    /**
     * A sitemap belongs to a website
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
