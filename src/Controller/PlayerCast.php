<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Vertex;
use App\Service\DocumentBroadcaster;
use App\Service\Mercure\Pusher;
use Exception;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Ctrl for WebSocket-controled Player Screen
 */
class PlayerCast extends AbstractController
{

    public function __construct(protected Pusher $pusher)
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

}
