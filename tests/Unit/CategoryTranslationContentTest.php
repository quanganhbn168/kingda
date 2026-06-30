<?php

namespace Tests\Unit;

use App\Models\CategoryTranslation;
use App\Services\Import\QuickCatalogJsonImporter;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CategoryTranslationContentTest extends TestCase
{
    public function test_category_content_is_mass_assignable(): void
    {
        $translation = new CategoryTranslation([
            'content' => '<h2>Nội dung danh mục</h2>',
        ]);

        $this->assertSame('<h2>Nội dung danh mục</h2>', $translation->content);
    }

    public function test_category_import_preserves_rich_content(): void
    {
        $method = new ReflectionMethod(QuickCatalogJsonImporter::class, 'categoryTranslationData');
        $data = $method->invoke(new QuickCatalogJsonImporter, [
            'slug' => 'danh-muc',
            'name' => 'Danh mục',
            'content' => '  <p>Nội dung chi tiết</p>  ',
        ]);

        $this->assertSame('<p>Nội dung chi tiết</p>', $data['content']);
        $this->assertArrayNotHasKey('canonical_url', $data);
        $this->assertSame('index,follow', $data['meta_robots']);
    }

    public function test_import_uses_the_english_slug_for_chinese_when_omitted(): void
    {
        $method = new ReflectionMethod(QuickCatalogJsonImporter::class, 'translations');
        $translations = $method->invoke(new QuickCatalogJsonImporter, [
            'translations' => [
                'vi' => ['name' => 'Mực in', 'slug' => 'muc-in'],
                'en' => ['name' => 'Printing Ink', 'slug' => 'printing-ink'],
                'zh' => ['name' => '印刷油墨'],
            ],
        ], 'muc-in', 'Danh mục #1');

        $this->assertSame('printing-ink', $translations['zh']['slug']);
    }
}
