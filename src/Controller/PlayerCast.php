<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\DocumentBroadcaster;
use App\Service\WebsocketPusher;
use Paragi\PhpWebsocket\ConnectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ctrl for WebSocket-controled Player Screen
 */
class PlayerCast extends AbstractController
{

    public function __construct(protected \App\Service\Mercure\Pusher $pusher)
    {
        
    }

    /**
     * The actual player screen updated with websocket
     */
    #[Route('/player/view', methods: ['GET'])]
    public function view(): Response
    {
        return $this->render('player/view.html.twig', ['url_picture' => $this->pusher->getUrlPicture()]);
    }

    /**
     * Returns a generated document
     */
    #[Route('/player/getdoc/{filename}', methods: ['GET'])]
    public function getDocument(string $filename, DocumentBroadcaster $broad): Response
    {
        return $broad->createResponseForFilename($filename);
    }

    //  /!\ -- Big security breach : internally called ONLY -- /!\
    // DO NOT EXPOSE THIS CONTROLLER PUBLICLY
    public function internalPushFile(string $pathname, string $imgType = 'picture'): JsonResponse
    {
        try {
            $pic = new \SplFileInfo($pathname);
            $this->pusher->sendPictureAsDataUrl($pic, $imgType);
            return new JsonResponse(['level' => 'success', 'message' => $pic->getBasename() . ' sent'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['level' => 'error', 'message' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

}
