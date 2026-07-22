<?php

namespace Tests\Feature;

use App\Enums\MetaRobots;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PostEditorialRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_order_is_assigned_automatically(): void
    {
        $category = $this->postCategory();

        $first = Post::query()->create(['category_id' => $category->id]);
        $second = Post::query()->create(['category_id' => $category->id]);

        $this->assertSame(10, $first->sort_order);
        $this->assertSame(20, $second->sort_order);
    }

    public function test_seo_metadata_is_generated_only_when_missing(): void
    {
        $post = Post::query()->create(['category_id' => $this->postCategory()->id]);
        $translation = PostTranslation::query()->create([
            'post_id' => $post->id,
            'locale' => 'vi',
            'title' => 'Tiêu đề <strong>bài viết</strong>',
            'content' => '<p>Nội dung <em>được dùng</em> để tạo mô tả SEO.</p>',
            'seo_title' => 'SEO title nhập tay',
            'meta_robots' => MetaRobots::NoindexFollow->value,
        ]);

        $this->assertSame('SEO title nhập tay', $translation->seo_title);
        $this->assertSame('Nội dung được dùng để tạo mô tả SEO.', $translation->seo_description);
        $this->assertSame(MetaRobots::NoindexFollow->value, $translation->meta_robots);
        $this->assertNotNull($translation->published_at);
        $this->assertDatabaseHas('post_translations', [
            'id' => $translation->id,
            'seo_title' => 'SEO title nhập tay',
            'seo_description' => 'Nội dung được dùng để tạo mô tả SEO.',
        ]);

        $translation->update([
            'title' => 'Tiêu đề đã đổi',
            'description' => 'Mô tả ngắn đã đổi.',
        ]);

        $translation->refresh();

        $this->assertSame('SEO title nhập tay', $translation->seo_title);
        $this->assertSame('Nội dung được dùng để tạo mô tả SEO.', $translation->seo_description);
        $this->assertSame('tieu-de-da-doi', $translation->slug);
        $this->assertSame($translation->seo_title, $translation->meta_title);
        $this->assertSame($translation->seo_description, $translation->meta_description);
    }

    public function test_meta_accessors_do_not_fall_back_to_editorial_content(): void
    {
        $translation = new PostTranslation([
            'title' => 'Tiêu đề chỉ để kiểm tra',
            'description' => 'Mô tả chỉ để kiểm tra',
        ]);

        $this->assertNull($translation->meta_title);
        $this->assertNull($translation->meta_description);
    }

    public function test_the_seo_backfill_migration_preserves_existing_post_metadata(): void
    {
        $post = Post::query()->create(['category_id' => $this->postCategory()->id]);

        $translationId = DB::table('post_translations')->insertGetId([
            'post_id' => $post->id,
            'locale' => 'vi',
            'slug' => 'bai-viet-cu',
            'title' => '<strong>Tiêu đề đã lưu</strong>',
            'description' => null,
            'content' => '<p>Nội dung dùng để đồng bộ SEO.</p>',
            'seo_title' => 'SEO cũ',
            'seo_description' => 'Mô tả SEO cũ',
            'meta_robots' => 'invalid-value',
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_07_22_010000_backfill_post_seo_metadata.php');
        $migration->up();

        $this->assertDatabaseHas('post_translations', [
            'id' => $translationId,
            'seo_title' => 'SEO cũ',
            'seo_description' => 'Mô tả SEO cũ',
            'meta_robots' => MetaRobots::IndexFollow->value,
        ]);
    }

    public function test_the_slug_backfill_migration_generates_a_unique_slug_for_old_posts(): void
    {
        $firstPost = Post::query()->create(['category_id' => $this->postCategory()->id]);
        PostTranslation::query()->create([
            'post_id' => $firstPost->id,
            'locale' => 'vi',
            'title' => 'Bài viết trùng slug',
        ]);

        $secondPost = Post::query()->create(['category_id' => $this->postCategory()->id]);
        $translationId = DB::table('post_translations')->insertGetId([
            'post_id' => $secondPost->id,
            'locale' => 'vi',
            'slug' => '',
            'title' => 'Bài viết trùng slug',
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_07_22_020000_backfill_missing_post_slugs.php');
        $migration->up();

        $this->assertDatabaseHas('post_translations', [
            'id' => $translationId,
            'slug' => 'bai-viet-trung-slug-2',
        ]);
    }

    public function test_empty_optional_translation_is_not_saved(): void
    {
        $post = Post::query()->create(['category_id' => $this->postCategory()->id]);
        $translation = new PostTranslation([
            'post_id' => $post->id,
            'locale' => 'en',
        ]);

        $this->assertFalse($translation->save());
        $this->assertDatabaseCount('post_translations', 0);
    }

    public function test_post_publication_is_controlled_by_the_post_status_not_translation_status(): void
    {
        $post = Post::query()->create([
            'category_id' => $this->postCategory()->id,
            'is_active' => false,
        ]);

        DB::table('post_translations')->insert([
            'post_id' => $post->id,
            'locale' => 'vi',
            'slug' => 'bai-viet-khong-cong-khai',
            'title' => 'Bài viết không công khai',
            'is_published' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(Post::query()->active()->withPublishedTranslation('vi')->exists());

        $post->update(['is_active' => true]);

        $this->assertTrue(Post::query()->active()->withPublishedTranslation('vi')->exists());
    }

    public function test_vietnamese_translation_requires_a_title(): void
    {
        $post = Post::query()->create(['category_id' => $this->postCategory()->id]);

        $this->expectException(ValidationException::class);

        PostTranslation::query()->create([
            'post_id' => $post->id,
            'locale' => 'vi',
        ]);
    }

    private function postCategory(): Category
    {
        return Category::query()->create([
            'type' => Category::TYPE_POST,
            'is_active' => true,
        ]);
    }
}
