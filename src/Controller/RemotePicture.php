<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\MediaWiki;
use App\Service\MwImageCache;
use App\Service\PlayerCastCache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for accessing remote file on the MediaWiki
 */
class RemotePicture extends AbstractController
{

    protected $remoteStorage;

    public function __construct(MwImageCache $mw)
    {
        $this->remoteStorage = $mw;
    }

    /**
     * Show image from MediaWiki
     */
    #[Route('/remote/get', methods: ['GET'])]
    public function read(Request $request): Response
    {
        $url = rawurldecode($request->query->get('url'));
        return $this->remoteStorage->get($url);
    }

    /**
     * Pushes a picture (from the remote MediaWiki) to player screen
     */
    #[Route('/remote/push', methods: ['POST'])]
    public function push(Request $request, PlayerCastCache $cache): JsonResponse
    {
        $url = rawurldecode($request->query->get('url'));
        $local = $this->remoteStorage->download($url);
        $picture = $cache->slimPictureForPush(imagecreatefromstring(file_get_contents($local->getPathname())));

        return $this->forward(GmPusher::class . '::internalPushPicture', [
                    'label' => $local->getBasename(),
                    'picture' => $picture
        ]);
    }

}
