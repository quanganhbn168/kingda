<?php

namespace App\Filament\Resources\PostCategories\Pages;

use App\Enums\CategoryType;
use App\Filament\Resources\PostCategories\PostCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostCategory extends CreateRecord
{
    protected static string $resource = PostCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = CategoryType::Post->value;

        return $data;
    }
}
