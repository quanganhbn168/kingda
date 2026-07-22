<?php

namespace App\Filament\Resources\PostCategories\Pages;

use App\Enums\CategoryType;
use App\Filament\Actions\PostCategoryDeleteActions;
use App\Filament\Resources\PostCategories\PostCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditPostCategory extends EditRecord
{
    protected static string $resource = PostCategoryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = CategoryType::Post->value;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            PostCategoryDeleteActions::single(),
        ];
    }
}
