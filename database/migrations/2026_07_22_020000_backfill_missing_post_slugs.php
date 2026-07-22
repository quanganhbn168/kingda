<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('post_translations')
            ->where(function ($query): void {
                $query->whereNull('slug')->orWhere('slug', '');
            })
            ->orderBy('id')
            ->eachById(function (object $translation): void {
                $baseSlug = Str::slug((string) $translation->title) ?: 'post';
                $slug = $baseSlug;
                $suffix = 2;

                while (DB::table('post_translations')
                    ->where('locale', $translation->locale)
                    ->where('slug', $slug)
                    ->exists()) {
                    $slug = $baseSlug.'-'.$suffix++;
                }

                DB::table('post_translations')
                    ->where('id', $translation->id)
                    ->update(['slug' => $slug]);
            });
    }

    public function down(): void
    {
        // Generated slugs are required for public post URLs and must be retained on rollback.
    }
};
