<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\CreationDag\DagFocusNode;
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

    #[Route('/list', methods: ['GET'])]
    public function list(): Response
    {
        $fullGraph = $this->provider->load();

        return $this->render('npcgraph/list.html.twig', ['graph' => $fullGraph]);
    }

    #[Route('/edit/{title}', methods: ['GET', "PUT"])]
    public function edit(string $title, Request $request): Response
    {
        return $this->processNodeForm($request, 'npcgraph/edit.html.twig', $title);
    }

    #[Route('/append', methods: ['GET', "PUT"])]
    public function append(Request $request): Response
    {
        return $this->processNodeForm($request, 'npcgraph/append.html.twig');
    }

    protected function processNodeForm(Request $request, string $template, ?string $title = null): Response
    {
        $fullGraph = $this->provider->load();

        $form = $this->createForm(\App\Form\CreationDag\FullTree::class, ['node' => $fullGraph]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->provider->save($form->getData());

            return $this->redirectToRoute('app_npcgraphcrud_list');
        }

        return $this->render($template, ['form' => $form->createView()]);
    }

}
