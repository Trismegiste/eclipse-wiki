<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\DocumentBroadcaster;
use App\Service\WebsocketPusher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ctrl for WebSocket-controled Player Screen
 */
class PlayerCast extends AbstractController
{

    protected $pusher;

    public function __construct(WebsocketPusher $fac)
    {
        $this->pusher = $fac;
    }

    /**
     * The actual player screen updated with websocket
     * @Route("/player/view", methods={"GET"})
     */
    public function view(): Response
    {
        return $this->render('player/view.html.twig', ['host' => $this->pusher->getUrl()]);
    }

    /**
     * Returns a generated document
     * @Route("/player/getdoc/{filename}", methods={"GET"})
     */
    public function getDocument(string $filename, DocumentBroadcaster $broad): Response
    {
        return $broad->createResponseForFilename($filename);
    }

}
