<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * LinkRender specialized for PDF rendering
 */
class PdfLinkRender extends LinkRender
{

    public function getImageInfo($info): array
    {
        $info['thumb'] = $this->routing->generate('get_picture', ['title' => $info['url']], UrlGeneratorInterface::ABSOLUTE_URL);
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
