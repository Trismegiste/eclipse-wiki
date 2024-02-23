<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\BattlemapDocument;
use App\Service\DocumentBroadcaster;
use App\Voronoi\HexaMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Client for the player
 */
#[Route('/player')]
class PlayerLog extends AbstractController
{

    /**
     * The SPA
     * @return Response
     */
    #[Route('/log')]
    public function index(): Response
    {
        $doc = new BattlemapDocument();
        (new HexaMap(25))->dumpMap($doc);

        return $this->render('player/journal.html.twig', ['doc' => $doc]);
    }

    /**
     * The peering page for the player, the player waits for GM peering action
     * @see GmPusher::peering()
     * @return Response
     */
    #[Route('/peering')]
    public function peering(): Response
    {
        return $this->render('player/peering.html.twig');
    }

    /**
     * Returns a generated document
     */
    #[Route('/getdoc/{filename}', methods: ['GET'])]
    public function getDocument(string $filename, DocumentBroadcaster $broad): Response
    {
        return $broad->createResponseForFilename($filename);
    }

}
