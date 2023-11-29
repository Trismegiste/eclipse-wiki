<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wikimedia\Parsoid\Mocks\MockPageConfig;
use Wikimedia\Parsoid\Mocks\MockPageContent;
use Wikimedia\Parsoid\Parsoid;

/**
 * Facade for Parsoid
 */
class Parser
{

    const parserOpts = [
        'body_only' => true,
        'wrapSections' => false,
        'discardDataParsoid' => true,
        'nativeTemplateExpansion' => true
    ];

    protected $parsoid;

    public function __construct(protected InternalDataAccess $access, protected UrlGeneratorInterface $router)
    {
        $siteConfig = new InternalSiteConfig([]);
        $siteConfig->registerExtensionModule([
            'class' => SymfonyBridge::class,
            'args' => [$this->router]
        ]);
        $this->parsoid = new Parsoid($siteConfig, $this->access);
    }

    public function parse(string $page): string
    {
        $pageContent = new MockPageContent(['main' => $page]);
        $pageConfig = new MockPageConfig([], $pageContent);

        return $this->parsoid->wikitext2html($pageConfig, self::parserOpts);
    }

}
