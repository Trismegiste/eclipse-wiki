<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Transhuman;
use App\Service\Mercure\SubscriptionClient;
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
    public function index(/* Transhuman $vertex */): Response
    {
        $topic = ['public'];
        //   $topic[] = 'player-' . $vertex->getPk();

        return $this->render('player/journal.html.twig', ['topic' => $topic]);
    }

    // for testing Sub API
    #[Route('/sub')]
    public function listing(SubscriptionClient $api): Response
    {
        return new Response(json_encode($api->getSubscriptions()));
    }

    #[Route('/qrcode/{pk}')]
    public function qrcode(Transhuman $vertex): Response
    {
        // new absolute URL
        return $this->render('player/qrcode.html.twig', ['url_cast' => $this->generateUrl('app_playerlog_index', ['pk' => $vertex->getPk()])]);
    }

}