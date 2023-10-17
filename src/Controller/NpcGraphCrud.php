<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\CreationDag\FullTree;
use App\Repository\CreationGraphProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/edit', methods: ['GET', "PUT"])]
    public function edit(Request $request): Response
    {
        $fullGraph = $this->provider->load();

        $form = $this->createForm(FullTree::class, ['node' => $fullGraph]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->provider->save($form['node']->getData());

            return $this->redirectToRoute('app_npcgraphcrud_run');
        }

        return $this->render('npcgraph/form.html.twig', ['form' => $form->createView()]);
    }

}
