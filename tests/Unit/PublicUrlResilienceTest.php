<?php

namespace Tests\Unit;

use App\Models\MenuItem;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Services\Frontend\FrontendUrlBuilder;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class PublicUrlResilienceTest extends TestCase
{
    public function test_an_orphaned_post_translation_has_no_public_url(): void
    {
        $translation = new PostTranslation([
            'locale' => 'vi',
            'slug' => 'bai-viet-test',
            'title' => 'Bài viết test',
        ]);
        $post = new Post;
        $post->setRelation('category', null);
        $post->setRelation('translations', new Collection([$translation]));
        $translation->setRelation('post', $post);

        $this->assertNull($translation->public_url);
        $this->assertNull($post->slug_url);
    }

    public function test_an_orphaned_product_translation_has_no_public_url(): void
    {
        $translation = new ProductTranslation([
            'locale' => 'vi',
            'slug' => 'san-pham-test',
            'name' => 'Sản phẩm test',
        ]);
        $product = new Product;
        $product->setRelation('category', null);
        $translation->setRelation('product', $product);

        $this->assertNull($translation->public_url);
    }

    public function test_url_builder_does_not_turn_an_item_slug_into_a_category_url(): void
    {
        $post = new Post;
        $post->setRelation('category', null);
        $postTranslation = new PostTranslation([
            'locale' => 'vi',
            'slug' => 'bai-viet-test',
            'title' => 'Bài viết test',
        ]);

        $product = new Product;
        $product->setRelation('category', null);
        $productTranslation = new ProductTranslation([
            'locale' => 'vi',
            'slug' => 'san-pham-test',
            'name' => 'Sản phẩm test',
        ]);

        $builder = app(FrontendUrlBuilder::class);

        $this->assertNull($builder->post($post, $postTranslation, 'vi'));
        $this->assertNull($builder->product($product, $productTranslation, 'vi'));
    }

    public function test_a_post_menu_item_uses_its_saved_fallback_when_no_public_url_exists(): void
    {
        $translation = new PostTranslation([
            'locale' => 'vi',
            'slug' => 'bai-viet-test',
            'title' => 'Bài viết test',
            'is_published' => true,
        ]);
        $post = new Post;
        $post->setRelation('category', null);
        $post->setRelation('translations', new Collection([$translation]));
        $translation->setRelation('post', $post);

        $menuItem = new MenuItem([
            'locale' => 'vi',
            'link_type' => 'post',
            'url' => '/tin-tuc',
        ]);
        $menuItem->setRelation('linkable', $post);

        $this->assertSame('/tin-tuc', $menuItem->resolved_url);
    }
}
