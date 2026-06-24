<?php

namespace App\Filament\Actions;

use App\Services\Import\QuickCatalogJsonImporter;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class QuickCatalogImportActions
{
    public static function productCategories(): Action
    {
        return Action::make('quickImportProductCategories')
            ->label('Nhập nhanh danh mục sản phẩm')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->slideOver()
            ->modalHeading('Nhập nhanh danh mục sản phẩm')
            ->modalDescription('Dán mảng JSON. Slug dùng để cập nhật bản ghi cũ và liên kết danh mục cha.')
            ->modalSubmitActionLabel('Nhập danh mục')
            ->schema([
                self::jsonField(self::categoryExample())
                    ->helperText('Bắt buộc: slug và translations. parent_slug có thể bỏ trống. Không nhập ảnh tại đây.'),
            ])
            ->action(function (array $data): void {
                $result = self::runImport(fn (): array => app(QuickCatalogJsonImporter::class)
                    ->importProductCategories($data['json_data']));

                Notification::make()
                    ->title('Đã nhập danh mục sản phẩm')
                    ->body(self::resultMessage($result))
                    ->success()
                    ->send();
            });
    }

    public static function products(): Action
    {
        return Action::make('quickImportProducts')
            ->label('Nhập nhanh sản phẩm')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->slideOver()
            ->stickyModalFooter()
            ->modalHeading('Nhập nhanh sản phẩm')
            ->modalDescription('Dán JSON hoặc tải file .json lên. Hệ thống tự kiểm tra toàn bộ dữ liệu rồi nhập đồng bộ theo từng batch 20 sản phẩm, không cần queue.')
            ->modalSubmitActionLabel('Bước 2: Bắt đầu nhập sản phẩm')
            ->schema([
                self::jsonUpload(),
                self::jsonField(self::productExample(), required: false)
                    ->helperText('Chỉ dùng ô này nếu không tải file. Sau khi chọn file, bấm nút Bước 2 ở thanh cố định phía dưới.')
                    ->rows(12),
            ])
            ->action(function (array $data, Action $action): void {
                try {
                    $result = self::importProducts($data);
                } catch (ValidationException $exception) {
                    self::notifyImportErrors($exception);
                    $action->halt();
                }

                Notification::make()
                    ->title('Đã nhập sản phẩm')
                    ->body(self::resultMessage($result))
                    ->success()
                    ->send();
            });
    }

    private static function jsonField(string $placeholder, bool $required = true): Textarea
    {
        return Textarea::make('json_data')
            ->label('Dữ liệu JSON')
            ->required($required)
            ->rows(26)
            ->placeholder($placeholder)
            ->hintAction(
                Action::make('loadJsonExample')
                    ->label('Nạp JSON mẫu')
                    ->icon('heroicon-o-document-text')
                    ->action(fn (Set $set) => $set('json_data', $placeholder))
            )
            ->extraAttributes(['class' => 'font-mono text-sm'])
            ->columnSpanFull();
    }

    private static function jsonUpload(): FileUpload
    {
        return FileUpload::make('json_file')
            ->label('File JSON')
            ->disk('local')
            ->directory('imports/catalog')
            ->acceptedFileTypes(['application/json', 'text/json', 'text/plain'])
            ->maxSize(10240)
            ->storeFileNamesIn('json_file_name')
            ->helperText('Bước 1: chọn file. Khi tên file hiện xong, bấm nút Bước 2 ở cuối panel. Tối đa 10 MB.')
            ->columnSpanFull();
    }

    private static function importProducts(array $data): array
    {
        $path = $data['json_file'] ?? null;

        $json = filled($path)
            ? Storage::disk('local')->get($path)
            : ($data['json_data'] ?? null);

        if (blank($json)) {
            throw ValidationException::withMessages([
                'json_file' => 'Hãy tải file JSON lên hoặc dán dữ liệu JSON.',
            ]);
        }

        $result = self::runImport(fn (): array => app(QuickCatalogJsonImporter::class)
            ->importProducts($json));

        if (filled($path)) {
            Storage::disk('local')->delete($path);
        }

        return $result;
    }

    private static function notifyImportErrors(ValidationException $exception): void
    {
        $errors = collect($exception->errors())->flatten()->values();
        $visibleErrors = $errors->take(8)->map(fn (string $error): string => '• '.$error)->implode("\n");
        $remaining = $errors->count() - 8;

        if ($remaining > 0) {
            $visibleErrors .= "\n• Và {$remaining} lỗi khác.";
        }

        Notification::make()
            ->title("Không thể nhập: {$errors->count()} lỗi dữ liệu")
            ->body($visibleErrors)
            ->danger()
            ->persistent()
            ->send();
    }

    private static function runImport(callable $callback): array
    {
        try {
            return $callback();
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'json_data' => 'Không thể nhập dữ liệu. Hãy kiểm tra SKU hoặc slug có bị trùng với bản ghi khác không.',
            ]);
        }
    }

    private static function resultMessage(array $result): string
    {
        $message = "Tạo mới: {$result['created']} · Cập nhật: {$result['updated']} · Bản dịch: {$result['translations']}";

        if (isset($result['batches'])) {
            $message .= " · Batch: {$result['batches']}";
        }

        return $message;
    }

    private static function categoryExample(): string
    {
        return <<<'JSON'
[
  {
    "slug": "muc-in",
    "parent_slug": null,
    "sort_order": 10,
    "is_active": true,
    "translations": {
      "vi": {
        "name": "Mực in",
        "slug": "muc-in",
        "description": "Mô tả tiếng Việt"
      },
      "en": {
        "name": "Printing inks",
        "slug": "printing-inks",
        "description": "English description"
      },
      "zh": {
        "name": "印刷油墨",
        "slug": "printing-inks-cn",
        "description": "中文描述"
      }
    }
  }
]
JSON;
    }

    private static function productExample(): string
    {
        return <<<'JSON'
[
  {
    "slug": "muc-in-abc",
    "category_slug": "muc-in",
    "sku": "INK-ABC",
    "sort_order": 10,
    "is_featured": false,
    "is_active": true,
    "translations": {
      "vi": {
        "name": "Mực in ABC",
        "slug": "muc-in-abc",
        "description": "Mô tả ngắn",
        "content": "<p>Nội dung chi tiết</p>",
        "specifications": {
          "Màu sắc": "Đen",
          "Đóng gói": "1 kg"
        },
        "blocks": {
          "applications": ["Điện tử 3C"],
          "features": ["Bám dính tốt"],
          "faq": []
        }
      },
      "en": {
        "name": "ABC printing ink",
        "slug": "abc-printing-ink"
      },
      "zh": {
        "name": "ABC印刷油墨",
        "slug": "abc-printing-ink-cn"
      }
    }
  }
]
JSON;
    }
}
