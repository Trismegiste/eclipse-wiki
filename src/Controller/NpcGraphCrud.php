<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Repository\CreationGraphProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * NPC creation with the help of creation graph
 */
#[Route('/npc/graph')]
class NpcGraphCrud extends AbstractController
{

    public function __construct(protected CreationGraphProvider $provider)
    {
        
    }

    #[Route('/run', methods: ['GET', 'POST'])]
    public function run(Request $request): Response
    {
        $fullGraph = $this->provider->load();

        return $this->render('npcgraph/run.html.twig', ['graph' => json_encode($fullGraph)]);
    }

    #[Route('/list', methods: ['GET'])]
    public function list(): Response
    {
        $fullGraph = $this->provider->load();

        return $this->render('npcgraph/list.html.twig', ['graph' => $fullGraph]);
    }

    #[Route('/edit/{title}', methods: ['GET', "PUT"])]
    public function edit(string $title, Request $request): Response
    {
        $fullGraph = $this->provider->load();

        $form = $this->createForm(\App\Form\CreationDag\DagFocusNode::class, $fullGraph, ['focus' => 0]);

        return $this->render('npcgraph/edit.html.twig', ['form' => $form->createView()]);
    }

}
