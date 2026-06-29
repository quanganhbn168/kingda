<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductCategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_empty_leaf_product_category_can_be_deleted(): void
    {
        $category = $this->category();

        $this->assertTrue($category->delete());
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_a_product_category_with_direct_products_cannot_be_deleted(): void
    {
        $category = $this->category();
        $product = Product::query()->create(['category_id' => $category->id]);

        $this->assertFalse($category->delete());
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_a_product_category_with_children_and_indirect_products_cannot_be_deleted(): void
    {
        $parent = $this->category();
        $child = $this->category($parent);
        $product = Product::query()->create(['category_id' => $child->id]);

        $this->assertFalse($parent->delete());
        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
        $this->assertDatabaseHas('categories', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $child->id,
        ]);
    }

    public function test_the_database_rejects_bypassing_the_model_for_a_category_with_products(): void
    {
        $category = $this->category();
        Product::query()->create(['category_id' => $category->id]);

        $this->expectException(QueryException::class);

        DB::table('categories')->where('id', $category->id)->delete();
    }

    public function test_the_database_rejects_bypassing_the_model_for_a_category_with_children(): void
    {
        $parent = $this->category();
        $this->category($parent);

        $this->expectException(QueryException::class);

        DB::table('categories')->where('id', $parent->id)->delete();
    }

    private function category(?Category $parent = null): Category
    {
        return Category::query()->create([
            'parent_id' => $parent?->id,
            'type' => Category::TYPE_PRODUCT,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }
}
