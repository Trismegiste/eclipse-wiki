<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Loveletter;
use App\Entity\Vertex;
use App\Form\LoveletterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Loveletter
 */
class LoveletterCrud extends GenericCrud
{

    protected function createEntity(string $title): Vertex
    {
        return new Loveletter($title);
    }

    /**
     * Creates a Love letter
     * @Route("/loveletter/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(LoveletterType::class, 'loveletter/create.html.twig', $request);
    }

    /**
     * Edits a Love letter
     * @Route("/loveletter/edit/{pk}", methods={"GET","PUT"})
     */
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(LoveletterType::class, 'loveletter/edit.html.twig', $pk, $request);
    }

    /**
     * Generate PDF for a Love letter
     * @Route("/loveletter/pdf/{pk}", methods={"GET"})
     */
    public function pdf(string $pk): Response
    {
        return new Response('ok');
    }

}
