<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductCategoryHierarchyRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_category_with_products_cannot_receive_a_child(): void
    {
        $parent = $this->category();
        Product::query()->create(['category_id' => $parent->id]);

        $this->expectException(ValidationException::class);

        $this->category($parent);
    }

    public function test_a_product_cannot_be_assigned_to_a_category_with_children(): void
    {
        $parent = $this->category();
        $this->category($parent);

        $this->expectException(ValidationException::class);

        Product::query()->create(['category_id' => $parent->id]);
    }

    public function test_a_leaf_category_can_receive_products(): void
    {
        $leaf = $this->category();
        $product = Product::query()->create(['category_id' => $leaf->id]);

        $this->assertSame($leaf->id, $product->category_id);
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
