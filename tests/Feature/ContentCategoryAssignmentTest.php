<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContentCategoryAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_post_cannot_be_saved_without_a_category(): void
    {
        $this->expectException(ValidationException::class);

        Post::query()->create();
    }

    public function test_a_post_cannot_use_a_product_category(): void
    {
        $category = Category::query()->create([
            'type' => Category::TYPE_PRODUCT,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);

        Post::query()->create(['category_id' => $category->id]);
    }

    public function test_a_product_cannot_be_saved_without_a_category(): void
    {
        $this->expectException(ValidationException::class);

        Product::query()->create();
    }

    public function test_an_existing_post_cannot_have_its_category_removed(): void
    {
        $category = Category::query()->create([
            'type' => Category::TYPE_POST,
            'is_active' => true,
        ]);
        $post = Post::query()->create(['category_id' => $category->id]);

        $this->expectException(ValidationException::class);

        $post->update(['category_id' => null]);
    }

    public function test_an_existing_product_cannot_have_its_category_removed(): void
    {
        $category = Category::query()->create([
            'type' => Category::TYPE_PRODUCT,
            'is_active' => true,
        ]);
        $product = Product::query()->create(['category_id' => $category->id]);

        $this->expectException(ValidationException::class);

        $product->update(['category_id' => null]);
    }

    public function test_orphaned_legacy_content_is_not_routable(): void
    {
        $postId = DB::table('posts')->insertGetId([
            'category_id' => null,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'category_id' => null,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(Post::query()->withRoutableCategory()->exists());
        $this->assertFalse(Product::query()->withRoutableCategory()->exists());

        $this->assertTrue(Post::query()->findOrFail($postId)->update(['is_active' => false]));
        $this->assertTrue(Product::query()->findOrFail($productId)->update(['is_active' => false]));
    }
}
