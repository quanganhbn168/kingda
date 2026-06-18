<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('menu_items')->select('menu_id')->distinct()->pluck('menu_id') as $menuId) {
            $groups = DB::table('menu_items')
                ->where('menu_id', $menuId)
                ->whereNotNull('group_key')
                ->get()
                ->groupBy('group_key');

            foreach ($groups as $groupKey => $rows) {
                foreach (['vi', 'en', 'zh'] as $locale) {
                    if ($rows->contains('locale', $locale)) {
                        continue;
                    }

                    $source = $rows->firstWhere('locale', 'vi') ?? $rows->first();

                    if (! $source) {
                        continue;
                    }

                    $parentGroupKey = $source->parent_id
                        ? DB::table('menu_items')->where('id', $source->parent_id)->value('group_key')
                        : null;

                    $parentId = $parentGroupKey
                        ? DB::table('menu_items')
                            ->where('menu_id', $menuId)
                            ->where('group_key', $parentGroupKey)
                            ->where('locale', $locale)
                            ->value('id')
                        : null;

                    DB::table('menu_items')->insert([
                        'menu_id' => $source->menu_id,
                        'group_key' => $groupKey,
                        'parent_id' => $parentId,
                        'locale' => $locale,
                        'label' => $source->label,
                        'link_type' => $source->link_type,
                        'linkable_type' => $source->linkable_type,
                        'linkable_id' => $source->linkable_id,
                        'url' => $source->url,
                        'target' => $source->target,
                        'rel' => $source->rel,
                        'icon' => $source->icon,
                        'css_class' => $source->css_class,
                        'is_active' => $source->is_active,
                        'sort_order' => $source->sort_order,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        //
    }
};
