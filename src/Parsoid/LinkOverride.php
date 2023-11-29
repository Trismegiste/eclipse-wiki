<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wikimedia\Parsoid\DOM\Element;
use Wikimedia\Parsoid\DOM\Node;
use Wikimedia\Parsoid\Ext\DOMProcessor;
use Wikimedia\Parsoid\Ext\DOMUtils;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;
use Wikimedia\Parsoid\Utils\DOMDataUtils;

/**
 * a DOMProcessor that append data attributes to DOMElement for brocasting pictures in the wikitext
 */
class LinkOverride extends DOMProcessor
{

    public function __construct(protected UrlGeneratorInterface $router)
    {
        
    }

    public function wtPostprocess(ParsoidExtensionAPI $extApi, Node $node, array $options): void
    {
        $child = $node->firstChild;
        while ($child) {
            if ($child instanceof Element) {
                if (DOMUtils::hasTypeOf($child, 'mw:File')) {
                    $this->processFile($child);
                } else if (DOMUtils::matchRel($child, '#^mw:WikiLink$#')) {
                    $this->processLink($child);
                } else {
                    $this->wtPostprocess($extApi, $child, $options);
                }
            }
            $child = $child->nextSibling;
        }
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
