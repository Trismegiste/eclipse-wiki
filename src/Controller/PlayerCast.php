<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\DocumentBroadcaster;
use App\Service\NetTools;
use App\Service\WebsocketFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Ctrl for WebSocket-controled Player Screen
 */
class PlayerCast extends AbstractController
{

    protected $factory;

    public function __construct(WebsocketFactory $fac)
    {
        $this->factory = $fac;
    }

    /**
     * The actual player screen updated with websocket
     * @Route("/player/view", methods={"GET"})
     */
    public function view(): Response
    {
        return $this->render('player/view.html.twig', ['host' => $this->factory->getUrl()]);
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
