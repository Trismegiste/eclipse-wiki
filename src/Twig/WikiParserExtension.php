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

    protected $parser;

    public function __construct(WikitextParser $wikiParser)
    {
        $this->parser = $wikiParser;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('wiki', [$this, 'printWikiText'], ['is_safe' => ['html']])
        ];
    }

    public function printWikiText(?string $wikitext): string
    {
        return empty($wikitext) ? '' : '<div class="parsed-wikitext">' . $this->parser->parse($wikitext) . '</div>';
    }
}
