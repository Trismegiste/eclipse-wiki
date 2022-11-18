<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Timeline;
use App\Entity\Vertex;
use App\Form\TimelineCreate;
use App\Form\VertexType;
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
     * @Route("/timeline/create", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(TimelineCreate::class, 'timeline/create.html.twig', $request);
    }

    /**
     * @Route("/timeline/edit/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     * @param string $pk
     * @param Request $request
     * @return Response
     */
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(VertexType::class, 'timeline/edit.html.twig', $pk, $request);
    }

    /**
     * Fragment : explore the graph and render the tree from a Timeline vertex
     * @param Timeline $root
     * @param string $mode the string 'tree' or 'toc'
     * @return Response
     */
    public function tree(Timeline $root, \App\Service\DigraphExplore $explorer, string $mode = 'tree'): Response
    {
        $template = ['tree' => 'tree', 'toc' => 'scene_toc'];
        $dump = $explorer->graphToSortedCategory($root);

        return $this->render('timeline/' . $template[$mode] . '.html.twig', ['network' => $dump]); // fail if template does not exist
    }

    /**
     * @Route("/timeline/pin/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function pin(string $pk, Request $request): Response
    {
        $timeline = $this->repository->load($pk);
        $request->getSession()->set('pinned_timeline', $timeline);
        $this->addFlash('success', 'Scenario ' . $timeline->getTitle() . ' épinglé');

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $pk]);
    }

    /**
     * @Route("/timeline/orphan", methods={"GET"})
     */
    public function showOrphan(\App\Service\DigraphExplore $explorer): Response
    {
        return $this->render('timeline/orphan.html.twig', ['orphan' => $explorer->findOrphan()]);
    }

}
