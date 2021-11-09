<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

/**
 * Description of LinkRender
 */
class LinkRender extends \Mike42\Wikitext\HtmlRenderer
{

    protected $routing;

    public function __construct(\Symfony\Component\Routing\Generator\UrlGeneratorInterface $routing)
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
