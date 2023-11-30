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

    protected function processFile(Element $node): void
    {
        $link = $node->firstChild;
        if ($link && ($link->nodeName === 'a')) {
            $img = $link->firstChild;
            if ($img && ($img->nodeName === 'img')) {
                $data = DOMDataUtils::getDataParsoid($img);
                if (preg_match('#^file:(.+)#', $data->sa['resource'], $matches)) {
                    $node->setAttribute('x-data', 'broadcast');
                    $link->setAttribute('href', $this->router->generate('app_picture_push', ['title' => $matches[1]]));
                    $link->setAttribute('x-bind', 'trigger');
                    $img->setAttribute('src', $this->router->generate('get_picture', ['title' => $matches[1]]));
                }
            }
        }
    }

    protected function processLink(Element $link): void
    {
        $data = DOMDataUtils::getDataParsoid($link);
        $link->setAttribute('href', $this->router->generate('app_wiki', ['title' => $data->sa['href']]));
    }

}
