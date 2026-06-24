<?php

namespace App\Services\Import;

use App\Enums\CategoryType;
use App\Enums\Locale;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JsonException;

class QuickCatalogJsonImporter
{
    private const PRODUCT_BATCH_SIZE = 20;

    public function importProductCategories(string $json): array
    {
        $items = $this->decode($json);

        return DB::transaction(function () use ($items): array {
            $result = ['created' => 0, 'updated' => 0, 'translations' => 0];
            $categoriesBySlug = [];
            $parentSlugs = [];

            foreach ($items as $index => $item) {
                $path = 'Danh mục #'.($index + 1);
                $slug = $this->requiredSlug($item['slug'] ?? null, "{$path}: thiếu slug.");
                $translations = $this->translations($item, $slug, $path);
                $category = $this->findProductCategory($slug);
                $wasRecentlyCreated = ! $category;

                $category ??= new Category;
                $category->fill([
                    'type' => CategoryType::Product->value,
                    'is_active' => (bool) ($item['is_active'] ?? true),
                    'sort_order' => (int) ($item['sort_order'] ?? 0),
                ])->save();

                foreach ($translations as $locale => $translation) {
                    $category->translations()->updateOrCreate(
                        ['locale' => $locale],
                        $this->categoryTranslationData($translation)
                    );
                    $result['translations']++;
                }

                $categoriesBySlug[$slug] = $category;
                $parentSlugs[$category->id] = filled($item['parent_slug'] ?? null)
                    ? $this->requiredSlug($item['parent_slug'], "{$path}: parent_slug không hợp lệ.")
                    : null;
                $result[$wasRecentlyCreated ? 'created' : 'updated']++;
            }

            foreach ($parentSlugs as $categoryId => $parentSlug) {
                if (! $parentSlug) {
                    Category::query()->whereKey($categoryId)->update(['parent_id' => null]);

                    continue;
                }

                $parent = $categoriesBySlug[$parentSlug] ?? $this->findProductCategory($parentSlug);

                if (! $parent) {
                    $this->fail("Không tìm thấy danh mục cha có slug '{$parentSlug}'.");
                }

                $category = Category::query()->findOrFail($categoryId);

                if ($parent->id === $categoryId || in_array($parent->id, $category->descendantIds(), true)) {
                    $this->fail("Danh mục '{$parentSlug}' không thể tự làm danh mục cha.");
                }

                Category::query()->whereKey($categoryId)->update(['parent_id' => $parent->id]);
            }

            return $result;
        });
    }

    public function importProducts(string $json): array
    {
        $items = $this->decode($json);
        $this->validateUniqueProductIdentifiers($items);
        $preparedItems = $this->prepareProducts($items);
        $result = ['created' => 0, 'updated' => 0, 'translations' => 0, 'batches' => 0];

        foreach (array_chunk($preparedItems, self::PRODUCT_BATCH_SIZE) as $batch) {
            $batchResult = DB::transaction(fn (): array => $this->importProductBatch($batch));

            foreach (['created', 'updated', 'translations'] as $key) {
                $result[$key] += $batchResult[$key];
            }

            $result['batches']++;
        }

        return $result;
    }

    private function prepareProducts(array $items): array
    {
        $prepared = [];
        $categories = [];

        foreach ($items as $index => $item) {
            $path = 'Sản phẩm #'.($index + 1);
            $slug = $this->requiredSlug($item['slug'] ?? null, "{$path}: thiếu slug.");
            $categorySlug = $this->requiredSlug(
                $item['category_slug'] ?? null,
                "{$path}: thiếu category_slug."
            );
            $category = $categories[$categorySlug] ??= $this->findProductCategory($categorySlug);

            if (! $category) {
                $this->fail("{$path}: không tìm thấy danh mục sản phẩm có slug '{$categorySlug}'.");
            }

            $translations = [];

            foreach ($this->translations($item, $slug, $path) as $locale => $translation) {
                $translations[$locale] = $this->productTranslationData($translation);
            }

            $prepared[] = [
                'slug' => $slug,
                'attributes' => [
                    'category_id' => $category->id,
                    'sku' => filled($item['sku'] ?? null) ? trim((string) $item['sku']) : null,
                    'price' => $this->nullableNumber($item['price'] ?? null, "{$path}: price không hợp lệ."),
                    'sale_price' => $this->nullableNumber($item['sale_price'] ?? null, "{$path}: sale_price không hợp lệ."),
                    'unit' => filled($item['unit'] ?? null) ? trim((string) $item['unit']) : null,
                    'is_featured' => (bool) ($item['is_featured'] ?? false),
                    'is_active' => (bool) ($item['is_active'] ?? true),
                    'sort_order' => (int) ($item['sort_order'] ?? 0),
                ],
                'translations' => $translations,
            ];
        }

        return $prepared;
    }

    private function importProductBatch(array $items): array
    {
        $result = ['created' => 0, 'updated' => 0, 'translations' => 0];

        foreach ($items as $item) {
            $sku = $item['attributes']['sku'];
            $product = $sku
                ? Product::query()->where('sku', $sku)->first()
                : $this->findProduct($item['slug']);
            $wasRecentlyCreated = ! $product;

            $product ??= new Product;
            $product->fill($item['attributes'])->save();

            foreach ($item['translations'] as $locale => $translation) {
                $product->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $translation
                );
                $result['translations']++;
            }

            $result[$wasRecentlyCreated ? 'created' : 'updated']++;
        }

        return $result;
    }

    private function validateUniqueProductIdentifiers(array $items): void
    {
        $seenSlugs = [];
        $seenSkus = [];
        $seenTranslationSlugs = [];
        $errors = [];

        foreach ($items as $index => $item) {
            $position = $index + 1;
            $slug = trim((string) ($item['slug'] ?? ''));
            $sku = trim((string) ($item['sku'] ?? ''));

            if ($slug !== '' && isset($seenSlugs[$slug])) {
                $errors[] = "Sản phẩm #{$position}: slug '{$slug}' bị trùng với sản phẩm #{$seenSlugs[$slug]}.";
            }

            if ($sku !== '' && isset($seenSkus[$sku])) {
                $errors[] = "Sản phẩm #{$position}: SKU '{$sku}' bị trùng với sản phẩm #{$seenSkus[$sku]}.";
            }

            if ($slug !== '') {
                $seenSlugs[$slug] = $position;
            }

            if ($sku !== '') {
                $seenSkus[$sku] = $position;
            }

            foreach (($item['translations'] ?? []) as $locale => $translation) {
                if (! is_array($translation)) {
                    continue;
                }

                $translationSlug = trim((string) ($translation['slug'] ?? ''));

                if ($translationSlug === '') {
                    continue;
                }

                $key = $locale.'|'.$translationSlug;

                if (isset($seenTranslationSlugs[$key])) {
                    $errors[] = "Sản phẩm #{$position}: translations.{$locale}.slug '{$translationSlug}' bị trùng với sản phẩm #{$seenTranslationSlugs[$key]}.";
                } else {
                    $seenTranslationSlugs[$key] = $position;
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['json_data' => $errors]);
        }
    }

    private function decode(string $json): array
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->fail('JSON không hợp lệ: '.$exception->getMessage());
        }

        $items = Arr::isList($decoded ?? []) ? $decoded : ($decoded['items'] ?? null);

        if (! is_array($items) || $items === []) {
            $this->fail('JSON phải là một mảng có ít nhất một phần tử hoặc object có khóa "items".');
        }

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                $this->fail('Phần tử #'.($index + 1).' phải là một object JSON.');
            }
        }

        return array_values($items);
    }

    private function translations(array $item, string $fallbackSlug, string $path): array
    {
        $input = $item['translations'] ?? null;

        if (! is_array($input) || $input === []) {
            $this->fail("{$path}: translations phải chứa ít nhất một ngôn ngữ.");
        }

        $translations = [];

        foreach (Locale::cases() as $locale) {
            $data = $input[$locale->value] ?? null;

            if ($data === null) {
                continue;
            }

            if (is_string($data)) {
                $data = ['name' => $data];
            }

            if (! is_array($data) || blank($data['name'] ?? null)) {
                $this->fail("{$path}: translations.{$locale->value}.name là bắt buộc.");
            }

            $data['name'] = trim((string) $data['name']);
            $data['slug'] = $this->requiredSlug(
                $data['slug'] ?? $fallbackSlug,
                "{$path}: translations.{$locale->value}.slug không hợp lệ."
            );
            $translations[$locale->value] = $data;
        }

        if ($translations === []) {
            $this->fail("{$path}: chỉ hỗ trợ translations.vi, translations.en và translations.zh.");
        }

        if (! collect($translations)->contains(fn (array $translation): bool => $translation['slug'] === $fallbackSlug)) {
            $this->fail("{$path}: slug chính phải trùng với slug của ít nhất một bản dịch.");
        }

        return $translations;
    }

    private function categoryTranslationData(array $data): array
    {
        return [
            'slug' => $data['slug'],
            'name' => $data['name'],
            'description' => $this->nullableString($data['description'] ?? null),
            'seo_title' => $this->nullableString($data['seo_title'] ?? null),
            'seo_description' => $this->nullableString($data['seo_description'] ?? null),
            'og_title' => $this->nullableString($data['og_title'] ?? null),
            'og_description' => $this->nullableString($data['og_description'] ?? null),
            'canonical_url' => $this->nullableString($data['canonical_url'] ?? null),
            'meta_robots' => $this->nullableString($data['meta_robots'] ?? null) ?: 'index,follow',
            'is_published' => (bool) ($data['is_published'] ?? true),
        ];
    }

    private function productTranslationData(array $data): array
    {
        foreach (['specifications', 'blocks'] as $field) {
            if (isset($data[$field]) && ! is_array($data[$field])) {
                $this->fail("Trường {$field} phải là object hoặc array JSON.");
            }
        }

        return [
            'slug' => $data['slug'],
            'name' => $data['name'],
            'description' => $this->nullableString($data['description'] ?? null),
            'content' => $this->nullableString($data['content'] ?? null),
            'specifications' => $data['specifications'] ?? null,
            'blocks' => $data['blocks'] ?? null,
            'seo_title' => $this->nullableString($data['seo_title'] ?? null),
            'seo_description' => $this->nullableString($data['seo_description'] ?? null),
            'og_title' => $this->nullableString($data['og_title'] ?? null),
            'og_description' => $this->nullableString($data['og_description'] ?? null),
            'canonical_url' => $this->nullableString($data['canonical_url'] ?? null),
            'meta_robots' => $this->nullableString($data['meta_robots'] ?? null) ?: 'index,follow',
            'is_published' => (bool) ($data['is_published'] ?? true),
            'published_at' => ($data['is_published'] ?? true) ? now() : null,
        ];
    }

    private function findProductCategory(string $slug): ?Category
    {
        return Category::query()
            ->where('type', CategoryType::Product->value)
            ->whereHas('translations', fn ($query) => $query->where('slug', $slug))
            ->first();
    }

    private function findProduct(string $slug): ?Product
    {
        return Product::query()
            ->whereHas('translations', fn ($query) => $query->where('slug', $slug))
            ->first();
    }

    private function requiredSlug(mixed $value, string $message): string
    {
        $slug = trim((string) $value, " \n\r\t\v\0/");

        if ($slug === '' || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $this->fail($message.' Slug chỉ gồm chữ thường không dấu, số và dấu gạch ngang.');
        }

        return $slug;
    }

    private function nullableNumber(mixed $value, string $message): int|float|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            $this->fail($message);
        }

        return $value + 0;
    }

    private function nullableString(mixed $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['json_data' => $message]);
    }
}
