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

    public function tree(Timeline $root, string $mode = 'tree'): Response
    {
        $tree = $this->repository->exploreTreeFrom($root);
        $intl = new \Collator($this->getParameter('kernel.default_locale'));

        $dump = [];
        foreach ($tree as $v) {
            $dump[$v->getCategory()][] = $v->getTitle();
        }
        foreach ($dump as $key => $v) {
            $intl->sort($dump[$key]);
        }

        return $this->render('timeline/' . ['tree' => 'tree', 'toc' => 'scene_toc'][$mode] . '.html.twig', ['network' => $dump]);
    }

    /**
     * @Route("/timeline/pin/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function pin(string $pk, Request $request): Response
    {
        $timeline = $this->repository->load($pk);
        $request->getSession()->set('pinned_timeline', $timeline);

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $pk]);
    }

}
