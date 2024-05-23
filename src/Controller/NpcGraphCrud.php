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

    /**
     * Uses the quick npc graph to quickly generates a new NPC
     * @param Request $request
     * @return Response
     */
    #[Route('/run', methods: ['GET', 'POST'])]
    public function run(Request $request): Response
    {
        $title = $request->query->get('title', '');
        $fullGraph = $this->provider->load();

        $form = $this->createForm(Selector::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->vertexRepo->save($npc);

            return $this->redirectToRoute('app_npcgenerator_edit', ['pk' => $npc->getPk()]);
        }

        return $this->render('npcgraph/run.html.twig', [
                    'graph' => $fullGraph,
                    'form' => $form->createView(),
                    'default_name' => mb_ucfirst($title)
        ]);
    }

    /**
     * Edits the quick graph NPC
     * @param Request $request
     * @return Response
     */
    #[Route('/edit', methods: ['GET', "PUT"])]
    public function edit(Request $request): Response
    {
        $fullGraph = $this->provider->load();

        $form = $this->createForm(FullTree::class, $fullGraph);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->provider->save($form->getData());

            return $this->redirectToRoute('app_npcgraphcrud_edit');
        }

        return $this->render('npcgraph/form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Deletes a node the quick npc graph, and removes all inbound edges
     * @param string $node
     * @param Request $request
     * @return Response
     */
    #[Route('/delete/{node}', methods: ['GET', "DELETE"])]
    public function delete(string $node, Request $request): Response
    {
        $fullGraph = $this->provider->load();

        $node2delete = $fullGraph->getNodeByName($node);
        $form = $this->createForm(\App\Form\CreationDag\DeleteNode::class, $fullGraph, ['selected' => $node2delete]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->provider->save($form->getData());

            return $this->redirectToRoute('app_npcgraphcrud_edit');
        }

        return $this->render('npcgraph/delete.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edits the quick graph NPC but for only ONE level and for ONE property of the node
     * @param int $level
     * @param string $propertyName
     * @param Request $request
     * @return Response
     */
    #[Route('/perlevel/{level}/{propertyName}', methods: ['GET', "PUT"])]
    public function perlevel(int $level, string $propertyName, Request $request): Response
    {
        $fullGraph = $this->provider->load();

        $form = $this->createForm(\App\Form\CreationDag\TreeLevelEdit::class, $fullGraph, [
            'property_name' => $propertyName,
            'level' => $level
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->provider->save($form->getData());

            return $this->redirectToRoute('app_npcgraphcrud_edit');
        }

        return $this->render('npcgraph/perlevel.html.twig', ['form' => $form->createView()]);
    }

}
