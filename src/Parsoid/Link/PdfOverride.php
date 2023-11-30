<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid\Link;

use App\Parsoid\LinkOverride;
use Wikimedia\Parsoid\DOM\Element;

/**
 * Overrides links for PDF output
 */
class PdfOverride extends LinkOverride
{

    protected function processFile(Element $node): void
    {
        
    }

    protected function processLink(Element $link): void
    {
        
    }

}
