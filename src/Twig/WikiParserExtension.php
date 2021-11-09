<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use Mike42\Wikitext\WikitextParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * WikiParserExtension parses wikitext
 */
class WikiParserExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('wiki', [$this, 'printWikiText'], ['is_safe' => ['html']])
        ];
    }

    public function printWikiText(string $wikitext): string
    {
        return WikitextParser::parse($wikitext);
    }

}
