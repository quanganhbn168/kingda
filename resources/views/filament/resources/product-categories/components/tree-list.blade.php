<div
    data-parent-id="{{ $parentId }}"
    x-sortable
    x-sortable-group="product-category-tree"
    x-on:start.stop="dragging = true"
    x-on:end.stop="
        dragging = false;
        $wire.moveCategory(
            Number($event.item.getAttribute('x-sortable-item')),
            $event.to.dataset.parentId === '' ? null : Number($event.to.dataset.parentId),
            $event.to.sortable.toArray().map(Number),
            $event.from.sortable.toArray().map(Number),
        );
    "
    @class([
        'space-y-2',
        'min-h-20 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/70 p-2 transition dark:border-gray-700 dark:bg-white/[0.03]' => $isRoot,
        'ml-7 mt-1 min-h-1 rounded-lg border-l-2 border-gray-200 pl-3 transition dark:border-gray-700' => ! $isRoot,
    ])
    x-bind:class="dragging ? 'border-primary-300 bg-primary-50/50 dark:border-primary-700 dark:bg-primary-950/20' : ''"
>
    @forelse ($nodes as $node)
        <div
            x-sortable-item="{{ $node['id'] }}"
            wire:key="product-category-tree-{{ $node['id'] }}"
            class="rounded-xl"
        >
            <div class="group flex items-center gap-2 rounded-xl border border-gray-200 bg-white p-2.5 shadow-sm transition hover:border-primary-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-700">
                <button
                    type="button"
                    x-sortable-handle
                    title="Kéo để di chuyển"
                    class="flex h-9 w-9 shrink-0 cursor-grab items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 active:cursor-grabbing dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                    <x-filament::icon icon="heroicon-o-bars-3" class="h-5 w-5" />
                </button>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($node['can_edit'])
                            <a href="{{ $node['edit_url'] }}" class="truncate text-sm font-semibold text-gray-950 hover:text-primary-600 dark:text-white dark:hover:text-primary-400">
                                {{ $node['name'] }}
                            </a>
                        @else
                            <span class="truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $node['name'] }}</span>
                        @endif

                        <x-filament::badge color="gray" size="sm">
                            {{ $node['products_count'] }} sản phẩm
                        </x-filament::badge>

                        @if (! $node['is_active'])
                            <x-filament::badge color="danger" size="sm">Đang ẩn</x-filament::badge>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-0.5">
                    @if ($node['can_move_up'])
                        <x-filament::icon-button
                            icon="heroicon-o-arrow-up"
                            color="gray"
                            size="sm"
                            label="Đưa lên"
                            wire:click="shiftCategory({{ $node['id'] }}, 'up')"
                        />
                    @endif

                    @if ($node['can_move_down'])
                        <x-filament::icon-button
                            icon="heroicon-o-arrow-down"
                            color="gray"
                            size="sm"
                            label="Đưa xuống"
                            wire:click="shiftCategory({{ $node['id'] }}, 'down')"
                        />
                    @endif

                    @if ($node['can_indent'])
                        <x-filament::icon-button
                            icon="heroicon-o-arrow-right"
                            color="gray"
                            size="sm"
                            label="Thụt vào làm danh mục con"
                            wire:click="shiftCategory({{ $node['id'] }}, 'indent')"
                        />
                    @endif

                    @if ($node['can_outdent'])
                        <x-filament::icon-button
                            icon="heroicon-o-arrow-left"
                            color="gray"
                            size="sm"
                            label="Đưa ra ngoài một cấp"
                            wire:click="shiftCategory({{ $node['id'] }}, 'outdent')"
                        />
                    @endif

                    @if ($node['can_edit'])
                        <x-filament::icon-button
                            :href="$node['edit_url']"
                            tag="a"
                            icon="heroicon-o-pencil-square"
                            color="primary"
                            size="sm"
                            label="Sửa danh mục"
                        />
                    @endif
                </div>
            </div>

            @if ($node['can_accept_children'])
                @include('filament.resources.product-categories.components.tree-list', [
                    'nodes' => $node['children'],
                    'parentId' => $node['id'],
                    'isRoot' => false,
                ])
            @else
                <div class="ml-10 mt-1 flex items-center gap-1.5 py-1 text-xs text-gray-400 dark:text-gray-500">
                    <x-filament::icon icon="heroicon-o-lock-closed" class="h-3.5 w-3.5" />
                    Đang chứa sản phẩm — không thể nhận danh mục con
                </div>
            @endif
        </div>
    @empty
        <div
            x-show="dragging"
            x-cloak
            class="flex min-h-12 items-center justify-center rounded-lg border border-dashed border-primary-300 px-3 py-2 text-center text-xs font-medium text-primary-600 dark:border-primary-700 dark:text-primary-400"
        >
            {{ $isRoot ? 'Thả vào đây để đưa về cấp gốc' : 'Thả vào đây để làm danh mục con' }}
        </div>

        @if ($isRoot)
            <div x-show="! dragging">
                <x-filament::empty-state
                    icon="heroicon-o-folder-open"
                    heading="Chưa có danh mục sản phẩm"
                    description="Hãy tạo danh mục đầu tiên để bắt đầu xây cây."
                />
            </div>
        @endif
    @endforelse
</div>
