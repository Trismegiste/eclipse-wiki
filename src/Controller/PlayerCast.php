<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ctrl for WebSocket-controled Player Screen
 */
class PlayerCast extends AbstractController
{

    /**
     * @Route("/player", methods={"GET"})
     */
    public function playerView(\App\Service\NetTools $utils): Response
    {
        return $this->render('player/view.html.twig', ['host' => $utils->getLocalIp()]);
    }

}
