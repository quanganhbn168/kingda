<x-filament-panels::page>
    <div class="menu-builder-page space-y-6">
        <x-filament::section heading="Thông tin menu">
            <div class="grid gap-4 md:grid-cols-4">
                <label class="grid gap-2 md:col-span-2">
                    <span class="text-sm font-medium text-gray-950 dark:text-white">Tên menu</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" wire:model.defer="menuForm.name" />
                    </x-filament::input.wrapper>
                </label>

                <label class="grid gap-2">
                    <span class="text-sm font-medium text-gray-950 dark:text-white">Vị trí</span>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.defer="menuForm.location">
                            <option value="header">Header</option>
                            <option value="footer">Footer</option>
                            <option value="mobile">Mobile</option>
                            <option value="sidebar">Sidebar</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </label>

                <label class="grid gap-2">
                    <span class="text-sm font-medium text-gray-950 dark:text-white">Thứ tự</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" wire:model.defer="menuForm.sort_order" />
                    </x-filament::input.wrapper>
                </label>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-950 dark:text-white">
                    <x-filament::toggle :state="$menuForm['is_active'] ? 'true' : 'false'" wire:click="$toggle('menuForm.is_active')" />
                    Kích hoạt menu
                </label>

                <x-filament::button wire:click="saveMenu" icon="heroicon-o-check">
                    Lưu menu
                </x-filament::button>
            </div>
        </x-filament::section>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-filament::section heading="Cây menu">
                <x-slot name="afterHeader">
                    <div class="flex flex-wrap gap-2">
                        @foreach (['vi' => 'VI', 'en' => 'EN', 'zh' => 'ZH'] as $locale => $label)
                            <x-filament::button
                                wire:click="setLocale('{{ $locale }}')"
                                :outlined="$activeLocale !== $locale"
                                size="sm"
                                :color="$activeLocale === $locale ? 'primary' : 'gray'"
                            >
                                {{ $label }}
                            </x-filament::button>
                        @endforeach
                    </div>
                </x-slot>

                <div class="mb-4 flex flex-wrap gap-2">
                    <x-filament::button wire:click="openCreateItemModal" icon="heroicon-o-plus">
                        Thêm item
                    </x-filament::button>
                </div>

                <div class="divide-y divide-gray-200 overflow-hidden rounded-xl border border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                    @forelse ($treeRows as $row)
                        <div
                            @class([
                                'flex flex-wrap items-center gap-3 px-4 py-3 transition',
                                'bg-primary-50 ring-1 ring-inset ring-primary-200 dark:bg-primary-950/30 dark:ring-primary-800' => $row['is_current'],
                                'bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/70' => ! $row['is_current'],
                            ])
                            style="padding-left: {{ 16 + ($row['depth'] * 28) }}px"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $row['label'] }}</span>
                                    <x-filament::badge color="gray" size="sm">{{ $row['link_type'] }}</x-filament::badge>

                                    @if ($row['is_current'])
                                        <x-filament::badge color="primary" size="sm">Active</x-filament::badge>
                                    @endif

                                    @if (! $row['is_active'])
                                        <x-filament::badge color="danger" size="sm">Ẩn</x-filament::badge>
                                    @endif
                                </div>

                                <div class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $row['url'] ?: 'Chưa có URL' }}
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1.5">
                                <x-filament::button wire:click="moveItem({{ $row['id'] }}, 'up')" size="xs" color="gray" outlined>
                                    Lên
                                </x-filament::button>

                                <x-filament::button wire:click="moveItem({{ $row['id'] }}, 'down')" size="xs" color="gray" outlined>
                                    Xuống
                                </x-filament::button>

                                <x-filament::button wire:click="openCreateItemModal({{ $row['id'] }})" size="xs" color="gray" outlined>
                                    Con
                                </x-filament::button>

                                <x-filament::button wire:click="openEditItemModal({{ $row['id'] }})" size="xs" color="gray">
                                    Sửa
                                </x-filament::button>

                                <x-filament::button wire:click="mountAction('deleteItem', { itemId: {{ $row['id'] }} })" size="xs" color="danger">
                                    Xóa
                                </x-filament::button>
                            </div>
                        </div>
                    @empty
                        <x-filament::empty-state
                            icon="heroicon-o-bars-3"
                            heading="Chưa có item"
                            description="Locale này chưa có item menu."
                        />
                    @endforelse
                </div>
            </x-filament::section>

            <div class="space-y-6">
                <x-filament::section heading="Import / Export JSON">
                    <x-slot name="afterHeader">
                        <div class="flex flex-wrap gap-2">
                            <x-filament::button wire:click="exportMenu" icon="heroicon-o-code-bracket" color="gray" outlined size="sm">
                                Xuất JSON
                            </x-filament::button>

                            <x-filament::button wire:click="downloadExport" icon="heroicon-o-arrow-down-tray" color="gray" outlined size="sm">
                                Tải file
                            </x-filament::button>
                        </div>
                    </x-slot>

                    <div class="grid gap-4">
                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-gray-950 dark:text-white">JSON xuất</span>
                            <x-filament::input.wrapper>
                                <textarea readonly rows="8" wire:model="exportJson" class="fi-input font-mono text-xs"></textarea>
                            </x-filament::input.wrapper>
                        </label>

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-gray-950 dark:text-white">JSON import</span>
                            <x-filament::input.wrapper>
                                <textarea rows="8" wire:model.defer="importJson" class="fi-input font-mono text-xs"></textarea>
                            </x-filament::input.wrapper>
                        </label>

                        <div>
                            <x-filament::button wire:click="importMenu" wire:confirm="Import sẽ thay toàn bộ item hiện tại của menu này. Tiếp tục?" icon="heroicon-o-arrow-up-tray" color="danger">
                                Import thay menu
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
