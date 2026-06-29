<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Services\Admin\ProductCategoryOptions;
use ReflectionMethod;
use Tests\TestCase;

class ProductCategoryOptionsTest extends TestCase
{
    public function test_tree_labels_use_indentation_at_every_depth(): void
    {
        $options = $this->buildOptions(leavesOnly: false);

        $this->assertSame([
            1 => 'Cha',
            2 => '　— Con',
            3 => '　　— Cháu',
            5 => '　　　— Chắt',
            6 => '　　　　— Chút',
            4 => '　— Con thứ hai',
        ], $options);
    }

    public function test_leaf_options_exclude_parents_and_include_the_full_path(): void
    {
        $options = $this->buildOptions(leavesOnly: true);

        $this->assertSame([
            6 => '　　　　— Cha / Con / Cháu / Chắt / Chút',
            4 => '　— Cha / Con thứ hai',
        ], $options);
    }

    private function buildOptions(bool $leavesOnly): array
    {
        $categories = collect([
            $this->category(1, null, 'Cha'),
            $this->category(2, 1, 'Con'),
            $this->category(3, 2, 'Cháu'),
            $this->category(4, 1, 'Con thứ hai'),
            $this->category(5, 3, 'Chắt'),
            $this->category(6, 5, 'Chút'),
        ]);

        $method = new ReflectionMethod(ProductCategoryOptions::class, 'build');

        return $method->invoke(new ProductCategoryOptions, $categories, $leavesOnly);
    }

    private function category(int $id, ?int $parentId, string $name): Category
    {
        $category = new Category([
            'parent_id' => $parentId,
            'type' => 'product',
        ]);
        $category->setAttribute('id', $id);
        $category->setRelation('translation', new CategoryTranslation([
            'locale' => 'vi',
            'name' => $name,
        ]));

        return $category;
    }
}
