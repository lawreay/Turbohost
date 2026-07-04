<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\SitemapGenerator;

/**
 * Returns the generated sitemap XML for search engines.
 */
class SitemapController extends Controller
{
    public function show(): void
    {
        $sitemap = new SitemapGenerator($this->config);
        $xml = $sitemap->generate();

        header('Content-Type: application/xml; charset=UTF-8');
        echo $xml;
    }
}
