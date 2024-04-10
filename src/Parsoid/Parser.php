<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use App\Parsoid\Internal\RpgPageConfig;
use App\Parsoid\Internal\RpgPageContent;

/**
 * Facade for Parsoid
 */
class Parser
{

    const parserOpts = [
        'body_only' => true,
        'wrapSections' => false,
        'discardDataParsoid' => true,
        'nativeTemplateExpansion' => true,
        'skipLanguageConversionPass' => true
    ];

    public function __construct(protected ParserFactory $factory)
    {
        
    }

    public function parse(string $page, string $target): string
    {
        $parser = $this->factory->create($target);
        $pageContent = new RpgPageContent($page);
        $pageConfig = new RpgPageConfig($pageContent);

        return $parser->wikitext2html($pageConfig, self::parserOpts);
    }

}
