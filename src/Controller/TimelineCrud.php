<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Timeline;
use App\Entity\Vertex;
use App\Form\TimelineType;
use App\Form\Type\WikiTreeType;
use App\Service\DigraphExplore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Timeline entity
 */
class TimelineCrud extends GenericCrud
{

    protected function createEntity(string $title): Vertex
    {
        return new Timeline($title);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/timeline/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate(\App\Form\TimelineCreate::class, 'timeline/create.html.twig', $request);
    }

    /**
     * @param string $pk
     * @param Request $request
     * @return Response
     */
    #[Route('/timeline/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(string $pk, Request $request): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $form = $this->createForm(TimelineType::class, $vertex, ['edit' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            $target = $form->get('create')->isClicked() ? 'app_vertexcrud_show' : 'app_timelinecrud_edit';

            return $this->redirectToRoute($target, ['pk' => $vertex->getPk()]);
        }

        return $this->render('timeline/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Fragment : explore the graph and render the tree from a Timeline vertex
     * @param Timeline $root
     * @param string $mode the string 'tree' or 'toc'
     * @return Response
     */
    public function tree(Timeline $root, DigraphExplore $explorer, string $mode = 'tree'): Response
    {
        $template = ['tree' => 'tree', 'toc' => 'scene_toc'];
        $dump = $explorer->graphToSortedCategory($root);

        return $this->render('timeline/' . $template[$mode] . '.html.twig', ['network' => $dump]); // fail if template does not exist
    }

    /**
     * Pin the current timeline in the user session
     */
    #[Route('/timeline/pin/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pin(Timeline $timeline, Request $request): Response
    {
        $request->getSession()->set('pinned_timeline', $timeline);
        $this->addFlash('success', 'Scenario ' . $timeline->getTitle() . ' épinglé');

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $timeline->getPk()]);
    }

}
