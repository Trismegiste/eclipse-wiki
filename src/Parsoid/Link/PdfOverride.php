<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid\Link;

use App\Parsoid\LinkOverride;
use App\Service\Storage;
use Wikimedia\Parsoid\DOM\Element;

/**
 * Overrides links for PDF output
 */
class PdfOverride extends LinkOverride
{

    public function __construct(protected Storage $storage)
    {
        
    }

    protected function transformFileDom(Element $container, Element $link, Element $img, string $wikiFilename)
    {
        $response = $this->storage->createResponse($wikiFilename);
        $img->setAttribute('src', 'file://' . $response->getFile()->getPathname());
    }

    protected function transformLinkDom(Element $link, string $wikilink)
    {
        $link->removeAttribute('href');
    }

    protected function transformMissingFileDom(Element $container, Element $link, Element $info, string $wikiFilename)
    {
        $container->removeChild($link);
    }

}
