<?php

namespace Tests\Feature;

use App\Services\Frontend\SitemapService;
use Mockery\MockInterface;
use Tests\TestCase;

class SitemapEndpointTest extends TestCase
{
    public function test_sitemap_is_returned_as_cached_xml(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>';

        $this->mock(SitemapService::class, function (MockInterface $mock) use ($xml): void {
            $mock->shouldReceive('render')->once()->andReturn($xml);
        });

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertHeader('Cache-Control', 'max-age=3600, public')
            ->assertSee($xml, escape: false);
    }

    public function test_robots_file_advertises_the_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_sitemap_stylesheet_is_returned_as_xml(): void
    {
        $this->get('/sitemap.xsl')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertHeader('Cache-Control', 'max-age=86400, public')
            ->assertSee('<xsl:stylesheet', escape: false)
            ->assertSee('sitemap:urlset', escape: false);
    }
}
