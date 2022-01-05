<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Handout;
use App\Entity\Vertex;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Handout
 */
class HandoutCrud extends GenericCrud
{

    protected function createEntity(string $title): Vertex
    {
        return new Handout($title);
    }

    /**
     * Creates a Handout
     * @Route("/handout/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(\App\Form\HandoutType::class, 'handout/create.html.twig', $request);
    }

    /**
     * Edits a Handout
     * @Route("/handout/edit/{pk}", methods={"GET","PUT"})
     */
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(\App\Form\HandoutType::class, 'handout/edit.html.twig', $pk, $request);
    }

}
