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
        $node = $extApi->wikitextToDOM($src, ['parseOpts' => ['extTag' => 'pushpublic']], false);
        $container = $doc->createElement('blockquote');
        $container->setAttribute('class', 'read-aloud');
        $container->setAttribute('x-data', "quoteBroadcasting");
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($container);
        // icon
        $icon = $doc->createElement('i');
        $icon->setAttribute('class', 'icon-push');
        $icon->setAttribute('data-pushable', 'pdf');
        $icon->setAttribute('x-on:click', "pushPdf");
        $container->appendChild($icon);
        $container->appendChild($node);

        return $fragment;
    }

}
