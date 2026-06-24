<?php

use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PostController;
use App\Http\Controllers\Frontend\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('set-locale')->group(function (): void {
    Route::get('/', [PageController::class, 'home'])->name('home');

    Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
    Route::get('/san-pham/{categorySlug}', [ProductController::class, 'category'])->name('products.category');
    Route::get('/san-pham/{categorySlug}/{productSlug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/tin-tuc', [PostController::class, 'index'])->name('news.index');
    Route::get('/tin-tuc/{categorySlug}', [PostController::class, 'category'])->name('posts.category');
    Route::get('/tin-tuc/{categorySlug}/{postSlug}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/linh-vuc', [PageController::class, 'industries'])->name('industries.index');
    Route::get('/linh-vuc/{slug}', [PageController::class, 'industryDetail'])->name('industries.show');
    Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
    Route::post('/lien-he', [PageController::class, 'storeContact'])->middleware('throttle:5,1')->name('contact.store');
    Route::get('/gioi-thieu', [PageController::class, 'about'])->name('about');
    Route::redirect('/ve-chung-toi', '/gioi-thieu', 301);

    Route::prefix('{locale}')
        ->whereIn('locale', ['en', 'zh'])
        ->group(function (): void {
            Route::get('/', [PageController::class, 'home'])->name('localized.home');

            Route::get('/products', [ProductController::class, 'index'])->name('localized.products.index');
            Route::get('/products/{categorySlug}', [ProductController::class, 'localizedCategory'])->name('localized.products.category');
            Route::get('/products/{categorySlug}/{productSlug}', [ProductController::class, 'localizedShow'])->name('localized.products.show');

            Route::get('/news', [PostController::class, 'index'])->name('localized.news.index');
            Route::get('/news/{categorySlug}', [PostController::class, 'localizedCategory'])->name('localized.posts.category');
            Route::get('/news/{categorySlug}/{postSlug}', [PostController::class, 'localizedShow'])->name('localized.posts.show');

            Route::get('/industries', [PageController::class, 'industries'])->name('localized.industries.index');
            Route::get('/industries/{slug}', [PageController::class, 'localizedIndustryDetail'])->name('localized.industries.show');

            Route::get('/contact', [PageController::class, 'contact'])->name('localized.contact');
            Route::post('/contact', [PageController::class, 'storeContact'])->middleware('throttle:5,1')->name('localized.contact.store');

            Route::get('/about', [PageController::class, 'about'])->name('localized.about');

            Route::get('/{slug}', [PageController::class, 'localizedShow'])
                ->where('slug', '^(?!admin$|api$|filament$|livewire.*|storage$|up$).+')
                ->name('localized.pages.show');
        });

    Route::get('/{slug}', [PageController::class, 'show'])
        ->where('slug', '^(?!admin$|api$|filament$|livewire.*|storage$|up$).+')
        ->name('pages.show');
});
