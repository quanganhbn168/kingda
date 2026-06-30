<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(SitemapService $sitemap): Response
    {
        return response($sitemap->render(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function stylesheet(): Response
    {
        $stylesheet = file_get_contents(resource_path('xml/sitemap.xsl'));

        abort_if($stylesheet === false, 404);

        return response($stylesheet, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow:',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
