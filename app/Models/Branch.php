<?php

namespace App\Models;

use App\Enums\BranchType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Branch extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    public const TYPE_HEAD_OFFICE = 'head_office';
    public const TYPE_BRANCH = 'branch';
    public const TYPE_SHOWROOM = 'showroom';
    public const TYPE_OFFICE = 'office';
    public const TYPE_FACTORY = 'factory';
    public const TYPE_WAREHOUSE = 'warehouse';

    protected $fillable = [
        'code',
        'type',
        'latitude',
        'longitude',
        'google_map_url',
        'google_map_embed',
        'is_head_office',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_head_office' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function types(): array
    {
        return BranchType::options();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(BranchTranslation::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(BranchContact::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function publicContacts(): HasMany
    {
        return $this->contacts()
            ->where('is_public', true);
    }

    public function translation(?string $locale = null): ?BranchTranslation
    {
        $locale ??= app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first();
    }

    public function primaryContact(?string $type = null): ?BranchContact
    {
        $query = $this->contacts()
            ->where('is_public', true)
            ->where('is_primary', true);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeHeadOffice(Builder $query): Builder
    {
        return $query->where('is_head_office', true);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->useDisk('public')->singleFile();

        $this->addMediaCollection('gallery')->useDisk('public');
    }
}
