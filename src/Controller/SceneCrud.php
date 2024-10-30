<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Form\SceneCreate;
use App\Form\VertexType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Scene entity
 */
class SceneCrud extends GenericCrud
{

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/scene/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate(SceneCreate::class, 'scene/create.html.twig', $request);
    }

    /**
     * @param string $pk
     * @param Request $request
     * @return Response
     */
    #[Route('/scene/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(VertexType::class, 'scene/edit.html.twig', $pk, $request);
    }

}
