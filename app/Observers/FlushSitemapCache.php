<?php

namespace App\Observers;

use App\Services\Frontend\SitemapService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class FlushSitemapCache implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        SitemapService::forgetCached();
    }

    public function deleted(Model $model): void
    {
        SitemapService::forgetCached();
    }

    public function restored(Model $model): void
    {
        SitemapService::forgetCached();
    }
}
