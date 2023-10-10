<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Repository\CreationGraphProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * NPC creation with the help of creation graph
 */
class NpcGraphCreation extends AbstractController
{

    public function __construct(protected CreationGraphProvider $provider)
    {
        
    }

    #[Route('/npc/quick/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->render('npcgraph/create.html.twig');
    }

}
