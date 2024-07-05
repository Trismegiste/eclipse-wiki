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
class DynamicPictureCache extends LocalFileCache
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
    }

    public function createProfilePic(Transhuman $npc, Request $request): Response
    {
        $cachedName = $this->createTargetFile(sprintf('profile-%s.png', $npc->getPk()));
        $etag = '"' . sha1(serialize($npc)) . '"';

        if (!$cachedName->isFile() || ($etag !== $request->headers->get('If-None-Match'))) {
            $pathname = $this->storage->getFileInfo($npc->tokenPic);
            $profile = $this->maker->generate($npc, $pathname);
            imagepng($profile, $cachedName->getPathname());

            $resp = new StreamedResponse(function () use ($profile) {
                        imagepng($profile);
                    });
        } else {
            $resp = new BinaryFileResponse($cachedName);
        }

        $this->appendEtagCache($resp, $etag);

        return $resp;
    }

}
