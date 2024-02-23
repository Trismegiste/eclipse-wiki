<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Service\GameSessionTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tracking actions for the current Game Session
 */
class GameSession extends AbstractController
{

    /**
     * Shows the history of the GM
     * @param GameSessionTracker $tracker
     * @return Response
     */
    public function history(GameSessionTracker $tracker): Response
    {
        return $this->render('gamesession/history.html.twig', ['document' => $tracker->getDocument()]);
    }

}
