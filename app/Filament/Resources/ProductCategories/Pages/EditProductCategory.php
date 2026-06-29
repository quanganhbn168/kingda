<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Enums\CategoryType;
use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductCategory extends EditRecord
{
    protected static string $resource = ProductCategoryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = CategoryType::Product->value;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
