<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->uuid('group_key')->nullable()->after('menu_id')->index();
        });

        $menus = DB::table('menu_items')->select('menu_id')->distinct()->pluck('menu_id');

        foreach ($menus as $menuId) {
            $items = DB::table('menu_items')
                ->where('menu_id', $menuId)
                ->orderBy('locale')
                ->orderBy('parent_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $groupKeysByPath = [];

            foreach ($items->groupBy('locale') as $localeItems) {
                $this->assignGroupKeysForTree($localeItems, null, '', $groupKeysByPath);
            }
        }
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('group_key');
        });
    }

    private function assignGroupKeysForTree($items, ?int $parentId, string $parentPath, array &$groupKeysByPath): void
    {
        $siblings = $items
            ->where('parent_id', $parentId)
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        foreach ($siblings as $index => $item) {
            $path = trim($parentPath . '.' . ($index + 1), '.');
            $groupKeysByPath[$path] ??= (string) Str::uuid();

            DB::table('menu_items')
                ->where('id', $item->id)
                ->update(['group_key' => $groupKeysByPath[$path]]);

            $this->assignGroupKeysForTree($items, $item->id, $path, $groupKeysByPath);
        }
    }
};
