<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Enums\Locale;
use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function beforeFill(): void
    {
        $this->record->loadMissing('translations');

        $fallback = $this->record->translations
            ->firstWhere('locale', Locale::Vietnamese->value)
            ?: $this->record->translations->first();

        foreach (Locale::cases() as $locale) {
            if ($this->record->translations->contains('locale', $locale->value)) {
                continue;
            }

            $this->record->translations()->create([
                'locale' => $locale->value,
                'title' => $fallback?->title ?: $this->record->key,
                'slug' => $this->record->is_home ? null : $fallback?->slug,
                'headline' => $fallback?->headline,
                'subheadline' => $fallback?->subheadline,
                'excerpt' => $fallback?->excerpt,
                'content' => $fallback?->content,
                'seo_title' => $fallback?->seo_title,
                'seo_description' => $fallback?->seo_description,
                'meta_robots' => $fallback?->meta_robots ?: 'index,follow',
                'canonical_url' => null,
                'og_title' => $fallback?->og_title,
                'og_description' => $fallback?->og_description,
                'is_published' => false,
                'published_at' => null,
            ]);
        }

        $this->record->unsetRelation('translations');
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
