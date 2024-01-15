<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\Mercure\SubscriptionClient;
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

    // for testing Sub API
    #[Route('/sub')]
    public function listing(SubscriptionClient $api): Response
    {
        return new Response(json_encode($api->getSubscriptions()));
    }

    #[Route('/peering')]
    public function peering(): Response
    {
        return $this->render('player/peering.html.twig');
    }

    #[Route('/hello', methods: ["POST"])]
    public function hello(Request $request, \App\Service\Mercure\Pusher $pusher): JsonResponse
    {
        $body = json_decode($request->getContent());
        $pusher->askPeering($body->identifier);

        return new JsonResponse(['status' => 'OK']);
    }

}
