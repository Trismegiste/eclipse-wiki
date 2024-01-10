<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        return $this->render('player/journal.html.twig');
    }

}
