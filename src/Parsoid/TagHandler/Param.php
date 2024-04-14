<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid\TagHandler;

use Wikimedia\Parsoid\Ext\ExtensionTagHandler;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;

/**
 * Parameters sheet <param>
 */
class Param extends ExtensionTagHandler
{

    public function sourceToDom(ParsoidExtensionAPI $extApi, string $src, array $extArgs)
    {
        $parameter = explode("\n", trim($src));

        $doc = $extApi->getTopLevelDoc();
        $table = $doc->createElement('table');
        $tbody = $doc->createElement('tbody');
        $table->appendChild($tbody);
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($table);

        foreach ($parameter as $row) {
            $param = explode(':', $row);
            $tr = $doc->createElement('tr');
            $tr->appendChild($doc->createElement('th', mb_convert_case(trim($param[0]), MB_CASE_TITLE,)));
            $tr->appendChild($doc->createElement('td', trim($param[1])));
            $tbody->appendChild($tr);
        }

        return $fragment;
    }

}
