<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use App\Entity\GameSessionDoc;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Managing the current Game Session Document
 */
class GameSessionTracker
{

    const SESSION_KEY = 'game_session';

    protected SessionInterface $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function getDocument(): GameSessionDoc
    {
        if (!$this->session->has(self::SESSION_KEY)) {
            $this->session->set(self::SESSION_KEY, new GameSessionDoc());
        }

        return $this->session->get(self::SESSION_KEY);
    }

}
