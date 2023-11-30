<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use App\Parsoid\Parser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

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

}
