<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\PlayerCastCache;
use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for pictures
 */
class Picture extends AbstractController
{

    protected $storage;

    public function __construct(Storage $store)
    {
        $this->storage = $store;
    }

    /**
     * Ajax for searching local images
     * @Route("/picture/search", methods={"GET"})
     */
    public function search(Request $request): JsonResponse
    {
        $title = $request->query->get('q', '');
        $it = $this->storage->searchByTitleContains($title);

        $choice = [];
        foreach ($it as $fch) {
            $choice[] = $fch->getBasename();
        }

        return new JsonResponse($choice);
    }

    /**
     * Show image from storage
     * @Route("/picture/get/{title}", name="get_picture", methods={"GET"})
     */
    public function read(string $title): Response
    {
        return $this->storage->createResponse($title);
    }

    /**
     * Pushes a picture (from the Storage) to player screen
     * @Route("/picture/push/{title}", methods={"POST"})
     */
    public function push(string $title, Storage $storage, PlayerCastCache $cache): JsonResponse
    {
        $picture = $cache->slimPictureForPush($storage->getFileInfo($title));

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $picture->getPathname()]);
    }

}
