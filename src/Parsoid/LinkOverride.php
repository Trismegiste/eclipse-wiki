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

    abstract protected function processFile(Element $node): void;

    abstract protected function processLink(Element $link): void;
}
