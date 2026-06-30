<?php

namespace Tests\Feature;

use App\Enums\Locale;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTranslationSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_chinese_category_slug_falls_back_to_the_english_slug(): void
    {
        $category = Category::query()->create([
            'type' => Category::TYPE_PRODUCT,
        ]);
        $category->translations()->create([
            'locale' => Locale::English->value,
            'name' => 'Printing Ink',
        ]);
        $chinese = $category->translations()->create([
            'locale' => Locale::Chinese->value,
            'name' => '印刷油墨',
        ]);

        $this->assertSame('printing-ink', $chinese->slug);
    }

    public function test_a_custom_chinese_slug_is_preserved(): void
    {
        $category = Category::query()->create([
            'type' => Category::TYPE_PRODUCT,
        ]);
        $chinese = $category->translations()->create([
            'locale' => Locale::Chinese->value,
            'name' => '印刷油墨',
            'slug' => 'custom-chinese-slug',
        ]);

        $this->assertSame('custom-chinese-slug', $chinese->slug);
    }
}
