<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid\Link;

use App\Parsoid\LinkOverride;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wikimedia\Parsoid\DOM\Element;
use Wikimedia\Parsoid\Utils\DOMDataUtils;

/**
 * A DOMProcessor that replaces all wikilinks to internal pages and 
 * to internal images to symfony controllers 
 * with the help of the symfony router
 */
class BrowserOverride extends LinkOverride
{

    public function __construct(protected UrlGeneratorInterface $router)
    {
        
    }

    protected function processLink(Element $link): void
    {
        $data = DOMDataUtils::getDataParsoid($link);
        $link->setAttribute('href', $this->router->generate('app_wiki', ['title' => $data->sa['href']]));
    }

    protected function transformFileDom(Element $container, Element $link, Element $img, string $wikiFilename)
    {
        $container->setAttribute('x-data', 'broadcast');
        $link->setAttribute('href', $this->router->generate('app_picture_push', ['title' => $wikiFilename]));
        $link->setAttribute('x-bind', 'trigger');
        $img->setAttribute('src', $this->router->generate('get_picture', ['title' => $wikiFilename]));
    }

}
