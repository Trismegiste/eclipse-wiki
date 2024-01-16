<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\Mercure\Pusher;
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
    public function index(/* Transhuman $vertex */): Response
    {
        return $this->render('player/journal.html.twig');
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

}
