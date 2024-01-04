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
                    'default_name' => mb_convert_case($title, MB_CASE_TITLE)
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

            return $this->redirectToRoute('app_npcgraphcrud_edit');
        }

        return $this->render('npcgraph/form.html.twig', ['form' => $form->createView()]);
    }

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

    #[Route('/essai', methods: ['GET', 'POST'])]
    public function essai(Request $request): Response
    {
        $form = $this->createFormBuilder()
                ->add('skills', \App\Form\Type\MultiCheckboxType::class, ['choices' => ['toto' => 3]])
                ->add('edges', \App\Form\Type\MultiCheckboxType::class, ['choices' => ['group' => ['titi' => 4]]])
                ->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'YOLO');
        }

        return $this->render('form.html.twig', ['form' => $form->createView(), 'title' => '']);
    }

}
