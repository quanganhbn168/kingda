<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Industry;
use App\Models\IndustryTranslation;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Observers\FlushSitemapCache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            Page::class,
            PageTranslation::class,
            Category::class,
            CategoryTranslation::class,
            Product::class,
            ProductTranslation::class,
            Post::class,
            PostTranslation::class,
            Industry::class,
            IndustryTranslation::class,
        ] as $model) {
            $model::observe(FlushSitemapCache::class);
        }
    }
}
