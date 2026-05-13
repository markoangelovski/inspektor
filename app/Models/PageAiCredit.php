<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageAiCredit extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'page_id',
        'url',
        'translatable_content',
        'word_count',
        'credits_one_language',
        'credits_five_languages',
        'calculated_at',
    ];

    protected $casts = [
        'translatable_content' => 'array',
        'calculated_at' => 'datetime',
        'word_count' => 'integer',
        'credits_one_language' => 'float',
        'credits_five_languages' => 'float',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
