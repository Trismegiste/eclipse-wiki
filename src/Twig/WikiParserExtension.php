<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use App\Parsoid\Parser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

/**
 * WikiParserExtension parses wikitext
 */
class WikiParserExtension extends AbstractExtension
{

    public function __construct(protected Parser $parser)
    {
        
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('wiki', [$this, 'printWikiText'], ['is_safe' => ['html']])
        ];
    }

    public function printWikiText(?string $wikitext, string $target = 'browser'): string
    {
        return empty($wikitext) ? '' : '<div class="parsed-wikitext">' . $this->parser->parse($wikitext, $target) . '</div>';
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('xpath', [$this, 'extractXpath'], ['is_safe' => ['html']]),
        ];
    }

    public function extractXpath(string $content, string $xpath): string
    {
        $xml = new \SimpleXMLElement($content);
        $result = $xml->xpath($xpath);

	return $result[0]->asXML();
    }

}
