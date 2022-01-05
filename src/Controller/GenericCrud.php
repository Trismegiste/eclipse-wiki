<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Vertex;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generic CRUD for Vertex subclasses
 */
abstract class GenericCrud extends AbstractController
{

    protected $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    abstract protected function createEntity(string $title): Vertex;

    abstract public function create(Request $request): Response;

    abstract public function edit(string $pk, Request $request): Response;

    protected function handleCreate(string $formClass, string $template, Request $request): Response
    {
        $obj = null;
        $fromLink = $request->query->has('title');
        if ($fromLink) {
            $title = $request->query->get('title');
            $obj = $this->createEntity(ucfirst($title));
        }

        $form = $this->createForm($formClass, $obj);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render($template, ['form' => $form->createView(), 'from_link' => $fromLink]);
    }

    protected function handleEdit(string $formClass, string $template, string $pk, Request $request): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $form = $this->createForm($formClass, $vertex, ['edit' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render($template, ['form' => $form->createView()]);
    }

}
