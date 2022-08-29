<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use Mike42\Wikitext\WikitextParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * WikiParserExtension for PDF
 */
class PdfWikiParserExtension extends AbstractExtension
{

    protected $parser;

    public function __construct(WikitextParser $wikiParser)
    {
        $this->parser = $wikiParser;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('wiki_for_pdf', [$this, 'printWikiText'], ['is_safe' => ['html']])
        ];
    }

    public function printWikiText(?string $wikitext): string
    {
        return empty($wikitext) ? '' : $this->parser->parse($wikitext);
    }

}
