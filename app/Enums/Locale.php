<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Locale: string
{
    use HasOptions;

    case Vietnamese = 'vi';
    case English = 'en';
    case Chinese = 'zh';

    public function label(): string
    {
        return match ($this) {
            self::Vietnamese => 'Tiếng Việt',
            self::English => 'English',
            self::Chinese => '中文',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Vietnamese => 'VI',
            self::English => 'EN',
            self::Chinese => '中文',
        };
    }

    public static function publicOptions(): array
    {
        return collect(self::cases())
            ->map(fn (self $case): array => [
                'locale' => $case->value,
                'name' => $case->label(),
                'label' => $case->shortLabel(),
            ])
            ->all();
    }

    public static function labelFor(?string $locale): ?string
    {
        if (! $locale) {
            return null;
        }

        return self::tryFrom($locale)?->label() ?? strtoupper($locale);
    }

    public static function translationRepeaterDefaults(): array
    {
        return collect(self::cases())
            ->map(fn (self $case): array => [
                'locale' => $case->value,
                'locale_label' => $case->label(),
            ])
            ->all();
    }

    public static function count(): int
    {
        return count(self::cases());
    }
}
