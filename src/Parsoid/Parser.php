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

    public function extractTagContent(string $wikitext, string $tagName, string $title): string
    {
        $xml = new \SimpleXMLElement("<wikitext>$wikitext</wikitext>");
        $result = $xml->xpath("//{$tagName}[@title=\"$title\"]");

        return $result[0]->asXml();
    }

    public function extractLocation(\App\Entity\Vertex $vertex): array
    {
        $html = $this->parse($vertex->getContent(), 'browser');
        $xml = new \SimpleXMLElement("<wikitext>$html</wikitext>");
        $result = $xml->xpath('//*[@typeof="mw:Transclusion"][@data-mw]');
        foreach ($result as $elem) {
            $template = json_decode((string) $elem->attributes()->{'data-mw'});
            if ($template?->parts[0]?->template?->target?->wt === 'location') {
                $param = (array) $template?->parts[0]?->template->params;
                array_walk($param, function (&$val) {
                    $val = $val->wt;
                });
                return $param;
            }
        }

        return [];
    }

}
