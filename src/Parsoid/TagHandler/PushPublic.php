<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid\TagHandler;

use Wikimedia\Parsoid\Ext\ExtensionTagHandler;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;

/**
 * A section validated to be pushed on public channel (a.k.a "read aloud" section) <pushpublic>
 */
class PushPublic extends ExtensionTagHandler
{

    public function sourceToDom(ParsoidExtensionAPI $extApi, string $src, array $extArgs)
    {
        $doc = $extApi->getTopLevelDoc();
        $node = $extApi->wikitextToDOM($src, ['parseOpts' => ['extTag' => 'carrousel', 'context' => 'inline']], false);
        $container = $doc->createElement('section');
        $container->setAttribute('class', 'read-aloud');
        $container->appendChild($node);
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($container);

        return $fragment;
    }

}
