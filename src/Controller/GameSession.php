<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\GameSessionDoc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tracking actions for the current Game Session
 */
class GameSession extends AbstractController
{

    const SESSION_KEY = 'game_session';

    public function history(Request $request): Response
    {
        $session = $request->getSession();
        if (!$session->has(self::SESSION_KEY)) {
            $session->set(self::SESSION_KEY, new GameSessionDoc());
        }

        return $this->render('gamesession/history.html.twig', ['document' => $session->get(self::SESSION_KEY)]);
    }

}
