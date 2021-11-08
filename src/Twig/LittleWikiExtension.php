<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wikimedia\LittleWikitext\LittleWikitext;

/**
 * Description of LittleWikiExtension
 */
class LittleWikiExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('littlewiki', [$this, 'printWikiText'], ['is_safe' => ['html']])
        ];
    }

    public function printWikiText(string $wikitext): string
    {
        $ast = LittleWikitext::markup2ast($wikitext);

        return $ast->toHtml();
    }

}
