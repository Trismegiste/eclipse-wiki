<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use App\Service\Storage;
use Mike42\Wikitext\HtmlRenderer;

/**
 * LinkRender specialized for PDF rendering
 */
class PdfLinkRender extends HtmlRenderer
{

    public function __construct(protected Storage $storage)
    {
        parent::__construct();
    }

    public function getImageInfo($info): array
    {
        $response = $this->storage->createResponse($info['url']);
        $info['thumb'] = 'file://' . $response->getFile()->getPathname();
        $info['url'] = '';
        $info['thumbnail'] = true;
        $info['caption'] = false;

        return $info;
    }

    public function getInternalLinkInfo($info): array
    {
        if (!$info['external']) {
            $info['url'] = '';
            $info['exists'] = true;
        } else {
            $info['caption'] = $info['target'];
        }

        return $info;
    }

}
