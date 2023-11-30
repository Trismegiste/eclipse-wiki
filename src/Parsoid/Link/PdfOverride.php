<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid\Link;

use App\Parsoid\LinkOverride;
use App\Service\Storage;
use Wikimedia\Parsoid\DOM\Element;
use Wikimedia\Parsoid\Utils\DOMDataUtils;

/**
 * Overrides links for PDF output
 */
class PdfOverride extends LinkOverride
{

    public function __construct(protected Storage $storage)
    {
        
    }

    protected function processFile(Element $node): void
    {
        $link = $node->firstChild;
        if ($link && ($link->nodeName === 'a')) {
            $img = $link->firstChild;
            if ($img && ($img->nodeName === 'img')) {
                $data = DOMDataUtils::getDataParsoid($img);
                if (preg_match('#^file:(.+)#', $data->sa['resource'], $matches)) {
                    $response = $this->storage->createResponse($matches[1]);
                    $img->setAttribute('src', 'file://' . $response->getFile()->getPathname());
                }
            }
        }
    }

    protected function processLink(Element $link): void
    {
        $link->removeAttribute('href');
    }

}
