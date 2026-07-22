<?php

use App\Enums\MetaRobots;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('post_translations')
            ->orderBy('id')
            ->eachById(function (object $translation): void {
                $title = $this->plainText($translation->title ?? null);
                $description = $this->plainText($translation->description ?? null)
                    ?: $this->plainText($translation->content ?? null)
                    ?: $title;
                $robots = MetaRobots::tryFrom((string) ($translation->meta_robots ?? ''))?->value
                    ?? MetaRobots::IndexFollow->value;
                $updates = [
                    'meta_robots' => $robots,
                ];

                if (blank($translation->seo_title ?? null)) {
                    $updates['seo_title'] = Str::limit($title, 255, '');
                }

                if (blank($translation->seo_description ?? null)) {
                    $updates['seo_description'] = Str::limit($description, 160, '');
                }

                DB::table('post_translations')
                    ->where('id', $translation->id)
                    ->update($updates);
            });
    }

    public function down(): void
    {
        // SEO data is generated from editorial content and must remain available on rollback.
    }

    private function plainText(?string $value): string
    {
        return Str::squish(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
};
