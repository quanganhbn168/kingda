<?php

namespace Tests\Unit;

use App\Models\Category;
use PHPUnit\Framework\TestCase;

class ProductCategoryDeletionRuleTest extends TestCase
{
    public function test_only_an_empty_leaf_product_category_can_be_deleted(): void
    {
        $category = $this->category(products: 0, children: 0);

        $this->assertTrue($category->canDeleteProductCategory());
        $this->assertNull($category->productCategoryDeletionBlockReason());
    }

    public function test_product_category_with_direct_products_is_blocked(): void
    {
        $category = $this->category(products: 2, children: 0);

        $this->assertFalse($category->canDeleteProductCategory());
        $this->assertStringContainsString('đang chứa sản phẩm', $category->productCategoryDeletionBlockReason());
    }

    public function test_product_category_with_children_is_blocked(): void
    {
        $category = $this->category(products: 0, children: 1);

        $this->assertFalse($category->canDeleteProductCategory());
        $this->assertStringContainsString('đang có danh mục con', $category->productCategoryDeletionBlockReason());
    }

    public function test_product_category_with_products_and_children_reports_both_reasons(): void
    {
        $category = $this->category(products: 1, children: 1);

        $this->assertFalse($category->canDeleteProductCategory());
        $this->assertStringContainsString('sản phẩm và danh mục con', $category->productCategoryDeletionBlockReason());
    }

    public function test_only_a_product_category_without_direct_products_can_accept_children(): void
    {
        $this->assertTrue($this->category(products: 0, children: 2)->canAcceptProductChildren());
        $this->assertFalse($this->category(products: 1, children: 0)->canAcceptProductChildren());
    }

    public function test_only_a_leaf_product_category_can_receive_products(): void
    {
        $this->assertTrue($this->category(products: 3, children: 0)->canReceiveProducts());
        $this->assertFalse($this->category(products: 0, children: 1)->canReceiveProducts());
    }

    private function category(int $products, int $children): Category
    {
        $category = new Category([
            'type' => Category::TYPE_PRODUCT,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $category->setAttribute('products_count', $products);
        $category->setAttribute('children_count', $children);

        return $category;
    }
}
