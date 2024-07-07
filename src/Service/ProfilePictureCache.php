<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use App\Entity\Transhuman;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

/**
 * Local cache for dynamic pictures generation
 */
class ProfilePictureCache extends LocalFileCache
{

    public function __construct(Filesystem $fs, string $folder, protected AvatarMaker $maker, protected Storage $storage)
    {
        parent::__construct($fs, $folder);
    }

    protected function appendEtagCache(Response $resp, string $etag): void
    {
        $resp->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        $resp->setEtag($etag);
        $resp->setPrivate();
        $resp->mustRevalidate();
        $resp->headers->set('content-type', 'image/png');
    }

    public function getPicture(Transhuman $npc, Request $request): Response
    {
        $cachedName = $this->createTargetFile(sprintf('profile-%s.png', $npc->getPk()));
        $etag = '"' . sha1(serialize($npc)) . '"';

        if ($cachedName->isFile() && ($etag === $request->headers->get('If-None-Match'))) {
            $resp = new BinaryFileResponse($cachedName);
            $resp::trustXSendfileTypeHeader();
        } else {
            $pathname = $this->storage->getFileInfo($npc->tokenPic);
            $profile = $this->maker->generate($npc, $pathname);

            $resp = new StreamedResponse(function () use ($profile, $cachedName) {
                        imagepng($profile);
                        imagepng($profile, $cachedName->getPathname());
                        imagedestroy($profile);
                    });
        }

        $this->appendEtagCache($resp, $etag);

        return $resp;
    }

}
