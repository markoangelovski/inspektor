<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
