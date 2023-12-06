<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use Wikimedia\Parsoid\DOM\Element;
use Wikimedia\Parsoid\DOM\Node;
use Wikimedia\Parsoid\Ext\DOMProcessor;
use Wikimedia\Parsoid\Ext\DOMUtils;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;
use Wikimedia\Parsoid\Utils\DOMDataUtils;

/**
 * a Generic DOMProcessor that replaces all wikilinks to internal pages and to internal pictures
 */
abstract class LinkOverride extends DOMProcessor
{

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

    protected function processLink(Element $link): void
    {
        $data = DOMDataUtils::getDataParsoid($link);
        $this->transformLinkDom($link, $data->sa['href']);
    }

    protected function processFile(Element $node): void
    {
        $link = $node->firstChild;
        if ($link && ($link->nodeName === 'a')) {
            if (!DOMUtils::hasTypeOf($node, 'mw:Error')) {
                $img = $link->firstChild;
                if ($img && ($img->nodeName === 'img')) {
                    $data = DOMDataUtils::getDataParsoid($img);
                    if (preg_match('#^file:(.+)#', $data->sa['resource'], $matches)) {
                        $this->transformFileDom($node, $link, $img, $matches[1]);
                    }
                }
            } else {
                $info = $link->firstChild;
                if ($info && ($info->nodeName === 'span')) {
                    $data = DOMDataUtils::getDataParsoid($info);
                    if (preg_match('#^file:(.+)#', $data->sa['resource'], $matches)) {
                        $this->transformMissingFileDom($node, $link, $info, $matches[1]);
                    }
                }
            }
        }
    }

    abstract protected function transformLinkDom(Element $link, string $wikilink);

    abstract protected function transformFileDom(Element $container, Element $link, Element $img, string $wikiFilename);

    abstract protected function transformMissingFileDom(Element $container, Element $link, Element $info, string $wikiFilename);
}
