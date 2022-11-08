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

    public function tree(Timeline $root): Response
    {
        $tree = $this->repository->exploreTimeline($root);
        $dump = [];
        foreach ($tree as $v) {
            $dump[$v->getCategory()][] = $v->getTitle();
        }
        foreach ($dump as $key => $v) {
            sort($dump[$key], SORT_LOCALE_STRING);
        }

        return $this->render('timeline/tree.html.twig', ['network' => $dump]);
    }

}
