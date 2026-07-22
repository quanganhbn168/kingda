<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostCategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_empty_post_category_can_be_deleted(): void
    {
        $category = $this->category();

        $this->assertTrue($category->delete());
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_a_post_category_with_posts_cannot_be_deleted(): void
    {
        $category = $this->category();
        $post = Post::query()->create(['category_id' => $category->id]);

        $this->assertFalse($category->delete());
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_a_post_category_with_children_cannot_be_deleted(): void
    {
        $parent = $this->category();
        $child = $this->category($parent);

        $this->assertFalse($parent->delete());
        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
        $this->assertDatabaseHas('categories', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_the_database_rejects_bypassing_the_model_for_a_category_with_posts(): void
    {
        $category = $this->category();
        Post::query()->create(['category_id' => $category->id]);

        $this->expectException(QueryException::class);

        DB::table('categories')->where('id', $category->id)->delete();
    }

    private function category(?Category $parent = null): Category
    {
        return Category::query()->create([
            'parent_id' => $parent?->id,
            'type' => Category::TYPE_POST,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }
}
