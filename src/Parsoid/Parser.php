<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use App\Entity\Vertex;
use Wikimedia\Parsoid\Mocks\MockPageConfig;
use Wikimedia\Parsoid\Mocks\MockPageContent;

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

    public function __construct(protected ParserFactory $factory)
    {
        
    }

    public function parse(string $page): string
    {
        $parser = $this->factory->create('browser');
        $pageContent = new MockPageContent(['main' => $page]);
        $pageConfig = new MockPageConfig([], $pageContent);

        return $parser->wikitext2html($pageConfig, self::parserOpts);
    }

    public function parseVertex(Vertex $vertex): string
    {
        $parser = $this->factory->create('browser');
        $pageContent = new MockPageContent(['main' => $vertex->getContent()]);
        $pageConfig = new MockPageConfig(['title' => $vertex->getTitle()], $pageContent);

        return $parser->wikitext2html($pageConfig, self::parserOpts);
    }

}
