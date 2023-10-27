<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\CreationDag\FullTree;
use App\Form\QuickNpc\Selector;
use App\Repository\CreationGraphProvider;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * NPC creation with the help of creation graph
 */
#[Route('/npc-graph')]
class NpcGraphCrud extends AbstractController
{

    public function __construct(protected CreationGraphProvider $provider, protected VertexRepository $vertexRepo)
    {
        
    }

    #[Route('/run', methods: ['GET', 'POST'])]
    public function run(Request $request): Response
    {
        $fullGraph = $this->provider->load();

        $form = $this->createForm(Selector::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->vertexRepo->save($npc);

            return $this->redirectToRoute('app_npcgenerator_edit', ['pk' => $npc->getPk()]);
        }

        return $this->render('npcgraph/run.html.twig', [
                    'graph' => json_encode($fullGraph->node),
                    'form' => $form->createView()
        ]);
    }

    #[Route('/edit', methods: ['GET', "PUT"])]
    public function edit(Request $request): Response
    {
        $fullGraph = $this->provider->load();

        $form = $this->createForm(FullTree::class, $fullGraph);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->provider->save($form->getData());

            return $this->redirectToRoute('app_npcgraphcrud_run');
        }

        return $this->render('npcgraph/form.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/delete/{node}', methods: ['GET', "DELETE"])]
    public function delete(string $node, Request $request): Response
    {
        $fullGraph = $this->provider->load();
    }

}
