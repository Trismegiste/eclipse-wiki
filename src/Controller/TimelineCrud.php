<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Timeline;
use App\Entity\Vertex;
use App\Form\TimelineCreate;
use App\Form\TimelineType;
use App\Service\DigraphExplore;
use App\Service\GameSessionTracker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Timeline entity
 */
#[Route('/timeline')]
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
    #[Route('/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate(TimelineCreate::class, 'timeline/create.html.twig', $request);
    }

    /**
     * @param string $pk
     * @param Request $request
     * @return Response
     */
    #[Route('/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
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
     * @return Response
     */
    public function tree(Timeline $root, DigraphExplore $explorer): Response
    {
        $dump = $explorer->graphToSortedCategory($root, 1);

        return $this->render('timeline/tree.html.twig', ['network' => $dump]);
    }

    /**
     * Pin the current timeline in the user session
     */
    #[Route('/pin/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pin(Timeline $timeline, GameSessionTracker $tracker): Response
    {
        $tracker->getDocument()->setTimeline($timeline);
        $this->addFlash('success', 'Scenario ' . $timeline->getTitle() . ' épinglé');

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $timeline->getPk()]);
    }

}
