<?php

namespace Tests\Unit;

use App\Enums\CategoryType;
use App\Filament\Resources\PostCategories\PostCategoryResource;
use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Tests\TestCase;

class CategoryResourceScopeTest extends TestCase
{
    public function test_post_category_resource_only_queries_post_categories(): void
    {
        $query = PostCategoryResource::getEloquentQuery();

        $this->assertStringContainsString('"type" = ?', $query->toSql());
        $this->assertSame([CategoryType::Post->value], $query->getBindings());
    }

    public function test_product_category_resource_only_queries_product_categories(): void
    {
        $query = ProductCategoryResource::getEloquentQuery();

        $this->assertStringContainsString('"type" = ?', $query->toSql());
        $this->assertSame([CategoryType::Product->value], $query->getBindings());
    }
}
