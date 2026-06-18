<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlideTranslation extends Model
{
    protected $fillable = [
        'slide_id',
        'locale',
        'eyebrow',
        'title',
        'description',
        'primary_button_label',
        'primary_button_url',
        'secondary_button_label',
        'secondary_button_url',
        'image_alt',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function slide(): BelongsTo
    {
        return $this->belongsTo(Slide::class);
    }

    public function scopeLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
