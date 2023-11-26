<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use Wikimedia\Parsoid\Mocks\MockPageConfig;
use Wikimedia\Parsoid\Mocks\MockPageContent;
use Wikimedia\Parsoid\Parsoid;

/**
 * Facade for Parsoid
 */
class Parser
{

    public function __construct(protected InternalDataAccess $access)
    {
        
    }

    public function parse(string $page): string
    {
        $opts = [];

        $parserOpts = [
            'body_only' => true,
            'wrapSections' => false,
            'discardDataParsoid' => true
        ];

        $siteConfig = new InternalSiteConfig($opts);
        $parsoid = new Parsoid($siteConfig, $this->access);

        $pageContent = new MockPageContent(['main' => $page]);
        $pageConfig = new MockPageConfig($opts, $pageContent);

        return $parsoid->wikitext2html($pageConfig, $parserOpts);
    }

}
