<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use Wikimedia\Parsoid\Ext\ExtensionTagHandler;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;

/**
 * Inline picture gallery tag <carrousel>
 */
class MorphBankTagHandler extends ExtensionTagHandler
{

    public function sourceToDom(ParsoidExtensionAPI $extApi, string $src, array $extArgs)
    {
        $inventory = explode("\n", trim($src));

        $doc = $extApi->getTopLevelDoc();
        $header = $doc->createElement('tr');
        foreach (['Morphe', 'Dispo', 'Stock'] as $title) {
            $cell = $doc->createElement('th', $title);
            $header->appendChild($cell);
        }


        $param = $extApi->extArgsToArray($extArgs);
        $caption = $doc->createElement('caption', $param['title']);
        $table = $doc->createElement('table');
        $tbody = $doc->createElement('tbody');
        $table->appendChild($header);
        $table->appendChild($caption);
        $table->appendChild($tbody);
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($table);

        foreach ($inventory as $idx => $row) {
            $morph = explode('|', $row);
            $tr = $doc->createElement('tr');
            $tr->appendChild($doc->createElement('td', $morph[0]));
            $tr->appendChild($doc->createElement('td', $morph[1]));
            $tr->appendChild($doc->createElement('td', $morph[2]));
            $tbody->appendChild($tr);
        }

        $icon = $doc->createElement('i');
        $icon->setAttribute('class', 'icon-push');
        $icon->setAttribute('data-pushable', 'pdf');
        $icon->setAttribute('data-title', $param['title']);
        $caption->appendChild($icon);

        return $fragment;
    }

}
