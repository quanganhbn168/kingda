<div
    class="space-y-4"
    x-data="{ dragging: false }"
>
    <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-600 ring-1 ring-gray-950/5 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10">
        <div class="flex gap-3">
            <x-filament::icon icon="heroicon-o-light-bulb" class="mt-0.5 h-5 w-5 shrink-0 text-primary-500" />
            <div class="space-y-1">
                <p><strong class="text-gray-950 dark:text-white">Kéo tay cầm</strong> để sắp xếp. Thả vào khung nét đứt bên dưới một danh mục để biến nó thành danh mục con.</p>
                <p>Các nút mũi tên là cách chỉnh chính xác hơn trên điện thoại. Thay đổi được lưu ngay, không cần bấm Lưu.</p>
            </div>
        </div>
    </div>

    <div class="relative">
        <div
            wire:loading.flex
            wire:target="moveCategory,shiftCategory"
            class="absolute inset-0 z-20 items-start justify-center rounded-xl bg-white/70 pt-12 backdrop-blur-[1px] dark:bg-gray-950/70"
        >
            <div class="flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-200 dark:ring-white/10">
                <x-filament::loading-indicator class="h-5 w-5" />
                Đang lưu cây danh mục…
            </div>
        </div>

        @include('filament.resources.product-categories.components.tree-list', [
            'nodes' => $tree,
            'parentId' => null,
            'isRoot' => true,
        ])
    </div>
</div>
