<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Service\GameSessionTracker;
use App\Service\SessionPushHistory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Tracking actions for the current Game Session
 */
class GameSession extends AbstractController
{

    public function __construct(protected SessionPushHistory $broadcastHistory)
    {
        
    }

    /**
     * Shows the history of the GM
     * @param GameSessionTracker $tracker
     * @return Response
     */
    public function history(GameSessionTracker $tracker): Response
    {
        return $this->render('gamesession/history.html.twig', ['document' => $tracker->getDocument()]);
    }

    #[Route('/session/broadcasted-picture', methods: ['GET'])]
    public function broadcastedListing(): Response
    {
        return $this->render('gamesession/broadcasted.html.twig', [
                    'listing' => $this->broadcastHistory->getListing()
        ]);
    }

    /**
     * Show broadcasted picture from cache
     */
    #[Route('/session/broadcasted-picture/{title}', methods: ['GET'])]
    public function broadcasted(string $title): Response
    {
        return $this->broadcastHistory->createResponse($title);
    }

}
