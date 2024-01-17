<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\BattlemapDocument;
use App\Service\DocumentBroadcaster;
use App\Service\Mercure\Pusher;
use App\Voronoi\HexaMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Client for the player
 */
#[Route('/player')]
class PlayerLog extends AbstractController
{

    #[Route('/log')]
    public function index(): Response
    {
        $doc = new BattlemapDocument();
        (new HexaMap(25))->dumpMap($doc);

        return $this->render('player/journal.html.twig', ['doc' => $doc]);
    }

    #[Route('/peering')]
    public function peering(): Response
    {
        return $this->render('player/peering.html.twig');
    }

    #[Route('/hello', methods: ["POST"])]
    public function hello(Request $request, Pusher $pusher): JsonResponse
    {
        $body = json_decode($request->getContent());
        $pusher->askPeering($body->identifier);

        return new JsonResponse(['status' => 'OK']);
    }

    /**
     * Returns a generated document
     */
    #[Route('/getdoc/{filename}', methods: ['GET'])]
    public function getDocument(string $filename, DocumentBroadcaster $broad): Response
    {
        return $broad->createResponseForFilename($filename);
    }

    /**
     * Send a ping on a relative position
     */
    #[Route('/ping-position', methods: ['POST'])]
    public function pingPosition(Request $request, Pusher $pusher): JsonResponse
    {
        $pos = json_decode($request->getContent());
        $pusher->pingRelativePosition($pos->deltaX, $pos->deltaY);

        return new JsonResponse(['status' => 'OK']);
    }

}
