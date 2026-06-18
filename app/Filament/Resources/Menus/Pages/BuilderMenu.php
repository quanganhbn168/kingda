<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Enums\CategoryType;
use App\Filament\Resources\Menus\MenuResource;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Page as SitePage;
use App\Models\Post;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BuilderMenu extends Page
{
    use InteractsWithRecord;

    protected static string $resource = MenuResource::class;

    protected string $view = 'filament.resources.menus.pages.builder-menu';

    public string $activeLocale = 'vi';

    public array $menuForm = [];

    public array $itemForm = [];

    public ?int $activeItemId = null;

    public ?string $activeGroupKey = null;

    public array $treeRows = [];

    public array $parentOptions = [];

    public string $exportJson = '';

    public string $importJson = '';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->authorizeAccess();
        $this->ensureMenuItemGroupKeys();
        $this->fillMenuForm();
        $this->reloadRows();
    }

    public function getTitle(): string
    {
        return 'Sửa menu: ' . $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
    }

    public function setLocale(string $locale): void
    {
        if (! in_array($locale, ['vi', 'en', 'zh'], true)) {
            return;
        }

        $this->activeLocale = $locale;
        $this->activeItemId = $this->activeGroupKey ? $this->itemIdForGroupAndLocale($this->activeGroupKey, $locale) : null;
        $this->reloadRows();
    }

    public function saveMenu(): void
    {
        $data = validator($this->menuForm, [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ])->validate();

        $this->getRecord()->update([
            'name' => $data['name'],
            'location' => $data['location'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        Notification::make()
            ->title('Đã lưu menu')
            ->success()
            ->send();
    }

    public function createItemAction(): Action
    {
        return Action::make('createItem')
            ->label('Thêm item')
            ->icon('heroicon-o-plus')
            ->slideOver()
            ->modalHeading('Thêm item menu')
            ->modalSubmitActionLabel('Thêm item')
            ->schema(fn (array $arguments): array => $this->itemModalSchema())
            ->fillForm(fn (array $arguments): array => $this->itemModalData($arguments))
            ->action(function (array $data, array $arguments): void {
                $this->saveItemData($data, (int) ($data['item_id'] ?? 0) ?: null);
            });
    }

    public function editItemAction(): Action
    {
        return Action::make('editItem')
            ->label('Sửa item')
            ->icon('heroicon-o-pencil-square')
            ->slideOver()
            ->modalHeading('Sửa item menu')
            ->modalSubmitActionLabel('Lưu thay đổi')
            ->schema(fn (array $arguments): array => $this->itemModalSchema((int) ($arguments['itemId'] ?? 0) ?: null))
            ->fillForm(fn (array $arguments): array => $this->itemModalData($arguments))
            ->action(function (array $data, array $arguments): void {
                $this->saveItemData($data, (int) ($data['item_id'] ?? $arguments['itemId'] ?? 0) ?: null);
            });
    }

    public function deleteItemAction(): Action
    {
        return Action::make('deleteItem')
            ->label('Xóa')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Xóa item menu')
            ->modalDescription('Item này và toàn bộ item con của nó sẽ bị xóa ở tất cả ngôn ngữ.')
            ->modalSubmitActionLabel('Xóa item')
            ->action(function (array $arguments): void {
                $this->deleteItem((int) $arguments['itemId']);
            });
    }

    public function openItemModal(?int $itemId = null, ?int $parentId = null): void
    {
        $this->activeItemId = $itemId ?: $parentId;
        $this->activeGroupKey = $this->activeItemId
            ? $this->getRecord()->items()->whereKey($this->activeItemId)->value('group_key')
            : null;

        $this->mountAction($itemId ? 'editItem' : 'createItem', array_filter([
            'itemId' => $itemId,
            'parentId' => $parentId,
        ], fn ($value): bool => filled($value)));
    }

    public function openCreateItemModal(?int $parentId = null): void
    {
        $this->openItemModal(parentId: $parentId);
    }

    public function openEditItemModal(int $itemId): void
    {
        $this->openItemModal(itemId: $itemId);
    }

    public function saveItemData(array $data, ?int $itemId = null): void
    {
        $data = validator($data, [
            'item_id' => ['nullable', 'integer'],
            'group_key' => ['nullable', 'string'],
            'parent_group_key' => ['nullable', 'string'],
            'translations' => ['required', 'array'],
            'translations.vi.label' => ['nullable', 'required_if:link_type,custom', 'string', 'max:255'],
            'translations.vi.url' => ['nullable', 'string', 'max:2048'],
            'translations.en.label' => ['nullable', 'string', 'max:255'],
            'translations.en.url' => ['nullable', 'string', 'max:2048'],
            'translations.zh.label' => ['nullable', 'string', 'max:255'],
            'translations.zh.url' => ['nullable', 'string', 'max:2048'],
            'link_type' => ['required', 'string', 'max:255'],
            'linkable_id' => ['nullable', 'integer'],
            'target' => ['nullable', 'string', 'max:32'],
            'rel' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'css_class' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ])->validate();

        $itemId = $itemId ?: (int) ($data['item_id'] ?? 0) ?: null;
        $item = $itemId ? $this->getRecord()->items()->whereKey($itemId)->first() : null;

        if (! $item && filled($data['group_key'] ?? null)) {
            $item = $this->getRecord()->items()
                ->where('group_key', $data['group_key'])
                ->where('locale', $this->activeLocale)
                ->first()
                ?? $this->getRecord()->items()
                    ->where('group_key', $data['group_key'])
                    ->first();
        }

        if ($itemId && ! $item) {
            $this->getRecord()->items()->whereKey($itemId)->firstOrFail();
        }

        $groupKey = filled($item?->group_key)
            ? $item->group_key
            : (filled($data['group_key'] ?? null) ? $data['group_key'] : (string) Str::uuid());

        if ($item && blank($item->group_key)) {
            $item->forceFill(['group_key' => $groupKey])->save();
        }

        $parentGroupKey = filled($data['parent_group_key'] ?? null) ? $data['parent_group_key'] : null;

        if ($parentGroupKey && $parentGroupKey === $groupKey) {
            Notification::make()
                ->title('Item không thể là cha của chính nó')
                ->danger()
                ->send();

            return;
        }

        if ($parentGroupKey && $this->isGroupDescendantOf($parentGroupKey, $groupKey)) {
            Notification::make()
                ->title('Không thể chọn item con làm cha')
                ->danger()
                ->send();

            return;
        }

        $sortOrder = $this->getRecord()->items()->where('group_key', $groupKey)->value('sort_order');
        $activeParentId = $parentGroupKey ? $this->itemIdForGroupAndLocale($parentGroupKey, $this->activeLocale) : null;
        $sortOrder ??= $this->nextSortOrder($activeParentId, $this->activeLocale);
        $linkable = $this->resolveLinkable($data['link_type'], $data['linkable_id'] ?? null);

        foreach (['vi', 'en', 'zh'] as $locale) {
            $translation = $data['translations'][$locale] ?? [];
            $label = filled($translation['label'] ?? null)
                ? $translation['label']
                : ($this->linkableLabel($linkable, $locale)
                    ?: $data['translations']['vi']['label']
                    ?: 'Menu item');

            $row = $this->getRecord()->items()
                ->where('group_key', $groupKey)
                ->where('locale', $locale)
                ->first() ?? new MenuItem([
                    'menu_id' => $this->getRecord()->id,
                    'group_key' => $groupKey,
                    'locale' => $locale,
                ]);

            $row->fill([
                'menu_id' => $this->getRecord()->id,
                'group_key' => $groupKey,
                'parent_id' => $parentGroupKey ? $this->itemIdForGroupAndLocale($parentGroupKey, $locale) : null,
                'locale' => $locale,
                'label' => $label,
                'link_type' => $data['link_type'],
                'linkable_type' => $linkable ? $linkable::class : null,
                'linkable_id' => $linkable?->id,
                'url' => $linkable ? null : (($translation['url'] ?? null) ?: null),
                'target' => $data['target'] ?: '_self',
                'rel' => $data['rel'] ?: null,
                'icon' => $data['icon'] ?: null,
                'css_class' => $data['css_class'] ?: null,
                'is_active' => (bool) ($data['is_active'] ?? false),
                'sort_order' => $sortOrder,
            ]);

            $row->save();

            if ($locale === $this->activeLocale) {
                $this->activeItemId = $row->id;
            }
        }

        $this->activeGroupKey = $groupKey;
        $this->reloadRows();

        Notification::make()
            ->title('Đã lưu item')
            ->success()
            ->send();
    }

    public function deleteItem(int $itemId): void
    {
        $item = $this->getRecord()->items()->whereKey($itemId)->firstOrFail();
        $groupKey = $item->group_key;

        DB::transaction(function () use ($item, $groupKey): void {
            foreach ($this->getRecord()->items()->where('group_key', $groupKey)->get() as $localeItem) {
                $this->deleteItemWithChildren($localeItem);
            }
        });

        $this->activeItemId = null;
        $this->activeGroupKey = null;
        $this->reloadRows();

        Notification::make()
            ->title('Đã xóa item')
            ->success()
            ->send();
    }

    public function moveItem(int $itemId, string $direction): void
    {
        $item = $this->getRecord()->items()->whereKey($itemId)->firstOrFail();
        $currentGroupKey = $item->group_key;
        $siblings = $this->getRecord()->items()
            ->where('locale', $item->locale)
            ->where('parent_id', $item->parent_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $index = $siblings->search(fn (MenuItem $sibling): bool => $sibling->id === $item->id);
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if (! $siblings->has($swapWith)) {
            return;
        }

        $other = $siblings->get($swapWith);
        $otherGroupKey = $other->group_key;
        [$itemOrder, $otherOrder] = [$item->sort_order, $other->sort_order];

        $this->getRecord()->items()->where('group_key', $currentGroupKey)->update(['sort_order' => $otherOrder]);
        $this->getRecord()->items()->where('group_key', $otherGroupKey)->update(['sort_order' => $itemOrder]);

        $this->reloadRows();
    }

    public function duplicateLocale(string $fromLocale, string $toLocale): void
    {
        if (! in_array($fromLocale, ['vi', 'en', 'zh'], true) || ! in_array($toLocale, ['vi', 'en', 'zh'], true) || $fromLocale === $toLocale) {
            return;
        }

        $items = $this->getRecord()->items()
            ->where('locale', $fromLocale)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get();

        DB::transaction(function () use ($items, $toLocale): void {
            $this->getRecord()->items()->where('locale', $toLocale)->delete();

            $idMap = [];

            foreach ($items as $item) {
                $copy = $item->replicate(['parent_id', 'locale']);
                $copy->menu_id = $this->getRecord()->id;
                $copy->locale = $toLocale;
                $copy->parent_id = null;
                $copy->save();
                $idMap[$item->id] = $copy->id;
            }

            foreach ($items as $item) {
                if (! $item->parent_id || ! isset($idMap[$item->parent_id], $idMap[$item->id])) {
                    continue;
                }

                MenuItem::whereKey($idMap[$item->id])->update(['parent_id' => $idMap[$item->parent_id]]);
            }
        });

        $this->activeLocale = $toLocale;
        $this->activeItemId = null;
        $this->activeGroupKey = null;
        $this->reloadRows();

        Notification::make()
            ->title('Đã copy menu sang ' . strtoupper($toLocale))
            ->success()
            ->send();
    }

    public function exportMenu(): void
    {
        $this->exportJson = json_encode($this->exportPayload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function downloadExport(): StreamedResponse
    {
        $payload = json_encode($this->exportPayload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fileName = Str::slug($this->getRecord()->name) . '-menu.json';

        return response()->streamDownload(function () use ($payload): void {
            echo $payload;
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    public function importMenu(): void
    {
        $payload = json_decode($this->importJson, true);

        if (! is_array($payload)) {
            Notification::make()
                ->title('JSON import không hợp lệ')
                ->danger()
                ->send();

            return;
        }

        $payload = $this->normalizeImportPayload($payload);

        DB::transaction(function () use ($payload): void {
            if (isset($payload['menu']) && is_array($payload['menu'])) {
                $this->getRecord()->update([
                    'name' => $payload['menu']['name'] ?? $this->getRecord()->name,
                    'location' => $payload['menu']['location'] ?? $this->getRecord()->location,
                    'is_active' => (bool) ($payload['menu']['is_active'] ?? $this->getRecord()->is_active),
                    'sort_order' => (int) ($payload['menu']['sort_order'] ?? $this->getRecord()->sort_order),
                ]);
            }

            $this->getRecord()->items()->delete();

            foreach (($payload['items'] ?? []) as $index => $item) {
                if (is_array($item)) {
                    $this->createImportedItem($item, null, $index);
                }
            }
        });

        $this->fillMenuForm();
        $this->activeItemId = null;
        $this->activeGroupKey = null;
        $this->reloadRows();
        $this->exportJson = '';

        Notification::make()
            ->title('Đã import menu')
            ->success()
            ->send();
    }

    protected function fillMenuForm(): void
    {
        $record = $this->getRecord();

        $this->menuForm = [
            'name' => $record->name,
            'location' => $record->location,
            'is_active' => $record->is_active,
            'sort_order' => $record->sort_order,
        ];
    }

    protected function ensureMenuItemGroupKeys(): void
    {
        $items = $this->getRecord()->items()
            ->orderBy('locale')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($items->every(fn (MenuItem $item): bool => filled($item->group_key))) {
            return;
        }

        $groupKeysByPath = [];

        foreach ($items->groupBy('locale') as $localeItems) {
            $this->assignMissingGroupKeysForTree($localeItems, null, '', $groupKeysByPath);
        }

        $this->record = $this->getRecord()->fresh();
    }

    protected function assignMissingGroupKeysForTree(Collection $items, ?int $parentId, string $parentPath, array &$groupKeysByPath): void
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

            if (filled($item->group_key)) {
                $groupKeysByPath[$path] ??= $item->group_key;
            } else {
                $groupKeysByPath[$path] ??= (string) Str::uuid();

                $item->forceFill(['group_key' => $groupKeysByPath[$path]])->save();
            }

            $this->assignMissingGroupKeysForTree($items, $item->id, $path, $groupKeysByPath);
        }
    }

    protected function reloadRows(): void
    {
        $items = $this->getRecord()->items()
            ->where('locale', $this->activeLocale)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $this->treeRows = $this->flattenItems($items);
        $this->refreshParentOptions();
    }

    protected function refreshParentOptions(): void
    {
        $this->parentOptions = $this->menuItemParentOptions($this->activeLocale, $this->activeGroupKey);
    }

    protected function itemModalSchema(?int $currentId = null): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Hidden::make('item_id'),
                    Hidden::make('group_key'),
                    Hidden::make('parent_group_key'),
                    TextInput::make('parent_label')
                        ->label('Cấp cha')
                        ->disabled()
                        ->dehydrated(false)
                        ->default('Cấp gốc')
                        ->columnSpanFull(),
                    Select::make('link_type')
                        ->label('Loại link')
                        ->options([
                            'custom' => 'Custom URL',
                            'page' => 'Trang',
                            'product_category' => 'Danh mục sản phẩm',
                            'post_category' => 'Danh mục tin tức',
                            'product' => 'Sản phẩm',
                            'post' => 'Bài viết',
                        ])
                        ->default('custom')
                        ->live()
                        ->required(),
                    Select::make('linkable_id')
                        ->label('Chọn nội dung')
                        ->options(fn (Get $get): array => $this->linkableOptions($get('link_type')))
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get): bool => $get('link_type') !== 'custom')
                        ->required(fn (Get $get): bool => $get('link_type') !== 'custom')
                        ->columnSpanFull(),
                    Select::make('target')
                        ->label('Target')
                        ->options([
                            '_self' => 'Cùng tab',
                            '_blank' => 'Tab mới',
                        ])
                        ->default('_self')
                        ->required(),
                    TextInput::make('rel')
                        ->label('Rel')
                        ->maxLength(255),
                    TextInput::make('icon')
                        ->label('Icon')
                        ->maxLength(255),
                    TextInput::make('css_class')
                        ->label('CSS class')
                        ->maxLength(255),
                    Toggle::make('is_active')
                        ->label('Hiển thị item')
                        ->default(true),
                    Tabs::make('translations_tabs')
                        ->label('Bản dịch')
                        ->tabs([
                            Tab::make('VI')
                                ->schema([
                                    TextInput::make('translations.vi.label')
                                        ->label('Label')
                                        ->required(fn (Get $get): bool => $get('../../link_type') === 'custom')
                                        ->maxLength(255),
                                    TextInput::make('translations.vi.url')
                                        ->label('URL')
                                        ->placeholder('/gioi-thieu')
                                        ->maxLength(2048)
                                        ->visible(fn (Get $get): bool => $get('../../link_type') === 'custom'),
                                ]),
                            Tab::make('EN')
                                ->schema([
                                    TextInput::make('translations.en.label')
                                        ->label('Label')
                                        ->maxLength(255),
                                    TextInput::make('translations.en.url')
                                        ->label('URL')
                                        ->placeholder('/en/about')
                                        ->maxLength(2048)
                                        ->visible(fn (Get $get): bool => $get('../../link_type') === 'custom'),
                                ]),
                            Tab::make('ZH')
                                ->schema([
                                    TextInput::make('translations.zh.label')
                                        ->label('Label')
                                        ->maxLength(255),
                                    TextInput::make('translations.zh.url')
                                        ->label('URL')
                                        ->placeholder('/zh/about')
                                        ->maxLength(2048)
                                        ->visible(fn (Get $get): bool => $get('../../link_type') === 'custom'),
                                ]),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected function itemModalData(array $arguments): array
    {
        $itemId = (int) ($arguments['itemId'] ?? 0);

        if ($itemId) {
            $item = $this->getRecord()->items()->whereKey($itemId)->firstOrFail();
            $this->activeItemId = $item->id;
            $this->activeGroupKey = $item->group_key;
            $translations = $this->translationsForGroup($item->group_key);

            return [
                'item_id' => $item->id,
                'group_key' => $item->group_key,
                'parent_group_key' => $item->parent?->group_key,
                'parent_label' => $item->parent?->label ?: 'Cấp gốc',
                'link_type' => $item->link_type,
                'linkable_id' => $item->linkable_id,
                'target' => $item->target ?: '_self',
                'rel' => $item->rel,
                'icon' => $item->icon,
                'css_class' => $item->css_class,
                'is_active' => $item->is_active,
                'translations' => $translations,
            ];
        }

        $parentId = (int) ($arguments['parentId'] ?? 0);
        $this->activeItemId = $parentId ?: null;
        $parent = $parentId ? $this->getRecord()->items()->whereKey($parentId)->first() : null;
        $this->activeGroupKey = $parent?->group_key;

        return [
            'item_id' => null,
            'group_key' => null,
            'parent_group_key' => $parent?->group_key,
            'parent_label' => $parent?->label ?: 'Cấp gốc',
            'link_type' => 'custom',
            'linkable_id' => null,
            'target' => '_self',
            'rel' => '',
            'icon' => '',
            'css_class' => '',
            'is_active' => true,
            'translations' => [
                'vi' => ['label' => '', 'url' => ''],
                'en' => ['label' => '', 'url' => ''],
                'zh' => ['label' => '', 'url' => ''],
            ],
        ];
    }

    protected function menuItemParentOptions(string $locale, ?string $currentGroupKey = null): array
    {
        return $this->getRecord()->items()
            ->where('locale', $locale)
            ->when($currentGroupKey, fn ($query) => $query->where('group_key', '!=', $currentGroupKey))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->reject(fn (MenuItem $item): bool => $currentGroupKey ? $this->isGroupDescendantOf($item->group_key, $currentGroupKey) : false)
            ->mapWithKeys(fn (MenuItem $item): array => [$item->group_key => str_repeat('— ', $this->itemDepth($item)) . $item->label])
            ->all();
    }

    protected function linkableOptions(?string $linkType): array
    {
        return match ($linkType) {
            'page' => SitePage::query()
                ->with('translations')
                ->ordered()
                ->get()
                ->mapWithKeys(fn (SitePage $page): array => [
                    $page->id => ($page->translation('vi')?->title ?: $page->key) . ' (' . $page->key . ')',
                ])
                ->all(),
            'product_category' => $this->categoryOptions(CategoryType::Product->value),
            'post_category' => $this->categoryOptions(CategoryType::Post->value),
            'product' => Product::query()
                ->with('translations')
                ->ordered()
                ->get()
                ->mapWithKeys(fn (Product $product): array => [
                    $product->id => $product->translationFor('vi')->first()?->name ?: $product->sku ?: 'Sản phẩm #' . $product->id,
                ])
                ->all(),
            'post' => Post::query()
                ->with('translations')
                ->ordered()
                ->get()
                ->mapWithKeys(fn (Post $post): array => [
                    $post->id => $post->translationFor('vi')->first()?->title ?: 'Bài viết #' . $post->id,
                ])
                ->all(),
            default => [],
        };
    }

    protected function categoryOptions(string $type): array
    {
        return Category::query()
            ->where('type', $type)
            ->with(['translations', 'parent.translations'])
            ->ordered()
            ->get()
            ->mapWithKeys(function (Category $category): array {
                $name = $category->translationFor('vi')->first()?->name ?: 'Danh mục #' . $category->id;
                $parent = $category->parent?->translationFor('vi')->first()?->name;

                return [$category->id => $parent ? $parent . ' / ' . $name : $name];
            })
            ->all();
    }

    protected function resolveLinkable(string $linkType, mixed $id): SitePage | Category | Product | Post | null
    {
        if (blank($id)) {
            return null;
        }

        return match ($linkType) {
            'page' => SitePage::query()->with('translations')->find((int) $id),
            'product_category' => Category::query()->where('type', CategoryType::Product->value)->with('translations')->find((int) $id),
            'post_category' => Category::query()->where('type', CategoryType::Post->value)->with('translations')->find((int) $id),
            'product' => Product::query()->with('translations')->find((int) $id),
            'post' => Post::query()->with('translations')->find((int) $id),
            default => null,
        };
    }

    protected function linkableLabel(SitePage | Category | Product | Post | null $linkable, string $locale): ?string
    {
        return match (true) {
            $linkable instanceof SitePage => $linkable->translation($locale)?->title,
            $linkable instanceof Category => $linkable->translationFor($locale)->first()?->name,
            $linkable instanceof Product => $linkable->translationFor($locale)->first()?->name,
            $linkable instanceof Post => $linkable->translationFor($locale)->first()?->title,
            default => null,
        };
    }

    protected function flattenItems(Collection $items, ?int $parentId = null, int $depth = 0): array
    {
        $rows = [];

        foreach ($items->where('parent_id', $parentId)->values() as $item) {
            $rows[] = [
                'id' => $item->id,
                'group_key' => $item->group_key,
                'parent_id' => $item->parent_id,
                'depth' => $depth,
                'label' => $item->label,
                'url' => $item->resolved_url,
                'link_type' => $item->link_type,
                'is_active' => $item->is_active,
                'is_current' => $this->activeGroupKey === $item->group_key,
            ];

            $rows = [
                ...$rows,
                ...$this->flattenItems($items, $item->id, $depth + 1),
            ];
        }

        return $rows;
    }

    protected function nextSortOrder(?int $parentId, string $locale): int
    {
        return ((int) $this->getRecord()->items()
            ->where('locale', $locale)
            ->where('parent_id', $parentId)
            ->max('sort_order')) + 10;
    }

    protected function itemIdForGroupAndLocale(?string $groupKey, string $locale): ?int
    {
        if (! $groupKey) {
            return null;
        }

        return $this->getRecord()->items()
            ->where('group_key', $groupKey)
            ->where('locale', $locale)
            ->value('id');
    }

    protected function translationsForGroup(?string $groupKey): array
    {
        $translations = [
            'vi' => ['label' => '', 'url' => ''],
            'en' => ['label' => '', 'url' => ''],
            'zh' => ['label' => '', 'url' => ''],
        ];

        if (! $groupKey) {
            return $translations;
        }

        $this->getRecord()->items()
            ->where('group_key', $groupKey)
            ->get()
            ->each(function (MenuItem $item) use (&$translations): void {
                $translations[$item->locale] = [
                    'label' => $item->label,
                    'url' => $item->url,
                ];
            });

        return $translations;
    }

    protected function isGroupDescendantOf(?string $candidateGroupKey, ?string $groupKey): bool
    {
        if (! $candidateGroupKey || ! $groupKey) {
            return false;
        }

        $candidate = $this->getRecord()->items()
            ->where('group_key', $candidateGroupKey)
            ->where('locale', $this->activeLocale)
            ->first();

        while ($candidate?->parent_id) {
            $parent = $this->getRecord()->items()->whereKey($candidate->parent_id)->first();

            if ($parent?->group_key === $groupKey) {
                return true;
            }

            $candidate = $parent;
        }

        return false;
    }

    protected function itemDepth(MenuItem $item): int
    {
        $depth = 0;
        $current = $item;

        while ($current->parent_id) {
            $depth++;
            $current = $this->getRecord()->items()->whereKey($current->parent_id)->first();

            if (! $current) {
                break;
            }
        }

        return $depth;
    }

    protected function deleteItemWithChildren(MenuItem $item): void
    {
        $item->children()->get()->each(fn (MenuItem $child) => $this->deleteItemWithChildren($child));
        $item->delete();
    }

    protected function isDescendantOf(int $candidateParentId, int $itemId): bool
    {
        $current = $this->getRecord()->items()->whereKey($candidateParentId)->first();

        while ($current) {
            if ((int) $current->parent_id === $itemId) {
                return true;
            }

            $current = $current->parent_id
                ? $this->getRecord()->items()->whereKey($current->parent_id)->first()
                : null;
        }

        return false;
    }

    protected function exportPayload(): array
    {
        $record = $this->getRecord()->fresh('items');

        return [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'menu' => Arr::only($record->toArray(), ['name', 'location', 'is_active', 'sort_order']),
            'items' => $this->exportItems($record->items->sortBy([['locale', 'asc'], ['sort_order', 'asc'], ['id', 'asc']])),
        ];
    }

    protected function exportItems(Collection $items, ?int $parentId = null): array
    {
        return $items->where('parent_id', $parentId)->values()->map(function (MenuItem $item) use ($items): array {
            return [
                'locale' => $item->locale,
                'group_key' => $item->group_key,
                'label' => $item->label,
                'link_type' => $item->link_type,
                'linkable_type' => $item->linkable_type,
                'linkable_id' => $item->linkable_id,
                'url' => $item->url,
                'target' => $item->target,
                'rel' => $item->rel,
                'icon' => $item->icon,
                'css_class' => $item->css_class,
                'is_active' => $item->is_active,
                'sort_order' => $item->sort_order,
                'children' => $this->exportItems($items, $item->id),
            ];
        })->all();
    }

    protected function normalizeImportPayload(array $payload): array
    {
        if (isset($payload['menus']) && is_array($payload['menus'])) {
            $payload = collect($payload['menus'])->firstWhere('location', $this->getRecord()->location) ?? $payload['menus'][0] ?? [];
        }

        $translations = $payload['translations'] ?? [];
        $defaultLocale = $payload['defaultLocale'] ?? $payload['locale'] ?? 'vi';
        $menuName = $payload['menu']['name']
            ?? $translations[$defaultLocale]['name']
            ?? $translations['vi']['name']
            ?? $payload['name']
            ?? $this->getRecord()->name;

        return [
            'menu' => [
                'name' => $menuName,
                'location' => $payload['menu']['location'] ?? $payload['location'] ?? $this->getRecord()->location,
                'is_active' => $payload['menu']['is_active'] ?? $payload['is_active'] ?? $payload['isActive'] ?? $this->getRecord()->is_active,
                'sort_order' => $payload['menu']['sort_order'] ?? $payload['sort_order'] ?? $this->getRecord()->sort_order,
            ],
            'items' => $payload['items'] ?? [],
        ];
    }

    protected function createImportedItem(array $data, int | array | null $parentId, int $fallbackOrder): void
    {
        $groupKey = $data['group_key'] ?? (string) Str::uuid();

        if (isset($data['translations']) && is_array($data['translations'])) {
            $this->createTranslatedImportedItem($data, $parentId, $fallbackOrder, $groupKey);

            return;
        }

        $item = $this->getRecord()->items()->create([
            'group_key' => $groupKey,
            'parent_id' => is_array($parentId) ? ($parentId[$this->activeLocale] ?? null) : $parentId,
            'locale' => $data['locale'] ?? $this->activeLocale,
            'label' => $data['label'] ?? $data['title'] ?? 'Menu item',
            'link_type' => $data['link_type'] ?? $data['type'] ?? 'custom',
            'linkable_type' => $data['linkable_type'] ?? null,
            'linkable_id' => $data['linkable_id'] ?? null,
            'url' => $data['url'] ?? $data['href'] ?? '#',
            'target' => $data['target'] ?? '_self',
            'rel' => $data['rel'] ?? (($data['relNofollow'] ?? $data['rel_nofollow'] ?? false) ? 'nofollow' : null),
            'icon' => $data['icon'] ?? null,
            'css_class' => $data['css_class'] ?? $data['cssClass'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $data['isActive'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? (($fallbackOrder + 1) * 10)),
        ]);

        foreach (($data['children'] ?? []) as $index => $child) {
            if (is_array($child)) {
                $this->createImportedItem($child, $item->id, $index);
            }
        }
    }

    protected function createTranslatedImportedItem(array $data, int | array | null $parentIds, int $fallbackOrder, string $groupKey): void
    {
        $translations = $data['translations'] ?? [];
        $activeLocales = $data['activeLocales'] ?? $data['active_locales'] ?? array_keys($translations);
        $createdByLocale = [];

        foreach (['vi', 'en', 'zh'] as $locale) {
            $translation = $translations[$locale] ?? [];
            $label = $translation['label'] ?? null;

            if (! $label && ! in_array($locale, $activeLocales, true)) {
                continue;
            }

            $created = $this->getRecord()->items()->create([
                'group_key' => $groupKey,
                'parent_id' => is_array($parentIds) ? ($parentIds[$locale] ?? null) : $parentIds,
                'locale' => $locale,
                'label' => $label ?: ($data['label'] ?? 'Menu item'),
                'link_type' => $data['link_type'] ?? $data['type'] ?? 'custom',
                'linkable_type' => $data['linkable_type'] ?? null,
                'linkable_id' => $data['linkable_id'] ?? null,
                'url' => $translation['url'] ?? $data['url'] ?? '#',
                'target' => $data['target'] ?? '_self',
                'rel' => $data['rel'] ?? (($data['relNofollow'] ?? $data['rel_nofollow'] ?? false) ? 'nofollow' : null),
                'icon' => $data['icon'] ?? null,
                'css_class' => $data['css_class'] ?? $data['cssClass'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? $data['isActive'] ?? true),
                'sort_order' => (int) ($data['sort_order'] ?? (($fallbackOrder + 1) * 10)),
            ]);

            $createdByLocale[$locale] = $created->id;
        }

        foreach (($data['children'] ?? []) as $index => $child) {
            if (is_array($child)) {
                $this->createImportedItem($child, $createdByLocale, $index);
            }
        }
    }
}
