<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use Wikimedia\Parsoid\Ext\ExtensionTagHandler;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;
use function str_starts_with;

/**
 * Inline picture gallery tag <carrousel>
 */
class CarrouselTagHandler extends ExtensionTagHandler
{

    public function sourceToDom(ParsoidExtensionAPI $extApi, string $src, array $extArgs)
    {
        $rows = explode("\n", trim($src));
        if (count($rows) % 2) {
            return $extApi->htmlToDom('The carrousel must have a even number of lines');
        }

        foreach ($rows as $idx => $row) {
            if ($idx % 2) {
                if (str_starts_with($row, '[[file:')) {
                    return $extApi->htmlToDom("There should be a caption instead of $row");
                }
            } else {
                if (!str_starts_with($row, '[[file:')) {
                    return $extApi->htmlToDom("There should be a picture instead of $row");
                }
            }
        }

        $doc = $extApi->getTopLevelDoc();
        $picture = $doc->createElement('tr');
        $caption = $doc->createElement('tr');
        $table = $doc->createElement('table');
        $table->appendChild($picture);
        $table->appendChild($caption);
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($table);
        for ($idx = 0; $idx < count($rows); $idx += 2) {
            // append picture
            $node = $extApi->wikitextToDOM($rows[$idx], ['parseOpts' => ['extTag' => 'carrousel', 'context' => 'inline']], false);
            $cell1 = $doc->createElement('td');
            $cell1->appendChild($node);
            $picture->appendChild($cell1);
            // append caption
            $cell2 = $doc->createElement('th', $rows[$idx + 1]);
            $caption->appendChild($cell2);
        }

        return $fragment;
    }

}
