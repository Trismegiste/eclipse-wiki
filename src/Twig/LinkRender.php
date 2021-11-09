<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use Mike42\Wikitext\HtmlRenderer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description of LinkRender
 */
class LinkRender extends HtmlRenderer
{

    protected $routing;

    public function __construct(UrlGeneratorInterface $routing)
    {
        $this->routing = $routing;
    }

    public function getImageInfo($info): array
    {
        return $info;
    }

    public function getInternalLinkInfo($info): array
    {
        $info['url'] = $this->routing->generate('app_wiki', ['title' => $info['title']]);

        return $info;
    }

}
