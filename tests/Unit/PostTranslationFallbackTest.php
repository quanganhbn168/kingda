<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Services\Frontend\FrontendUrlBuilder;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class PostTranslationFallbackTest extends TestCase
{
    public function test_missing_locale_uses_the_published_vietnamese_translation(): void
    {
        $post = new Post;
        $post->setRelation('translations', new Collection([
            new PostTranslation([
                'locale' => 'vi',
                'slug' => 'bai-viet-tieng-viet',
                'title' => 'Bài viết Tiếng Việt',
                'is_published' => true,
            ]),
        ]));

        $this->assertSame('vi', $post->resolveTranslation('en', publishedOnly: true)?->locale);
    }

    public function test_an_explicitly_unpublished_locale_does_not_fall_back(): void
    {
        $post = new Post;
        $post->setRelation('translations', new Collection([
            new PostTranslation([
                'locale' => 'vi',
                'slug' => 'bai-viet-tieng-viet',
                'title' => 'Bài viết Tiếng Việt',
                'is_published' => true,
            ]),
            new PostTranslation([
                'locale' => 'en',
                'slug' => 'english-post',
                'title' => 'English post',
                'is_published' => false,
            ]),
        ]));

        $this->assertNull($post->resolveTranslation('en', publishedOnly: true));
    }

    public function test_a_fallback_post_uses_its_vietnamese_public_url(): void
    {
        $vietnameseCategory = new CategoryTranslation([
            'locale' => 'vi',
            'slug' => 'danh-muc-viet',
            'name' => 'Danh mục Việt',
            'is_published' => true,
        ]);
        $englishCategory = new CategoryTranslation([
            'locale' => 'en',
            'slug' => 'english-category',
            'name' => 'English category',
            'is_published' => true,
        ]);
        $category = new Category(['type' => Category::TYPE_POST]);
        $category->setRelation('translation', $englishCategory);
        $category->setRelation('translations', new Collection([
            $vietnameseCategory,
            $englishCategory,
        ]));

        $post = new Post;
        $post->setRelation('category', $category);
        $post->setRelation('translations', new Collection([
            new PostTranslation([
                'locale' => 'vi',
                'slug' => 'bai-viet-tieng-viet',
                'title' => 'Bài viết Tiếng Việt',
                'is_published' => true,
            ]),
        ]));
        $post->useResolvedTranslation('en', publishedOnly: true);

        $url = app(FrontendUrlBuilder::class)->post($post, $post->translation, 'en');

        $this->assertSame(url('/tin-tuc/danh-muc-viet/bai-viet-tieng-viet'), $url);
        $this->assertSame(url('/tin-tuc/danh-muc-viet/bai-viet-tieng-viet'), $post->translation->public_url);
    }
}
