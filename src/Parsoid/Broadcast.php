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
 * a DOMProcessor that append data attributes to DOMElement for brocasting pictures in the wikitext
 */
class Broadcast extends DOMProcessor
{

    public function wtPostprocess(ParsoidExtensionAPI $extApi, Node $node, array $options): void
    {
        $child = $node->firstChild;
        while ($child) {
            if ($child instanceof Element) {
                if (DOMUtils::hasTypeOf($child, 'mw:File')) {
                    $this->processFile($child);
                } else {
                    $this->wtPostprocess($extApi, $child, $options);
                }
            }
            $child = $child->nextSibling;
        }
    }

    protected function processFile(Element $node)
    {
        $link = $node->firstChild;
        if ($link && ($link->nodeName === 'a')) {
            $img = $link->firstChild;
            if ($img && ($img->nodeName === 'img')) {
                $data = DOMDataUtils::getDataParsoid($img);
                if (preg_match('#^file:(.+)#', $data->sa['resource'], $matches)) {
                    $node->setAttribute('x-data', json_encode(['filename' => $matches[1]]));
                    $link->setAttribute('x-on:click.prevent', 'alert(filename)');
                }
            }
        }
    }

}
