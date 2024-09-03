<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Synopsis;
use App\Entity\Vertex;
use App\Form\SynopsisType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Generates synopsis with LLM
 */
#[Route('/synopsis')]
class SynopsisCrud extends GenericCrud
{

    protected function createEntity(string $title): Vertex
    {
        return new Synopsis($title);
    }

    /**
     * Creates a synopsis
     */
    #[Route('/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate(SynopsisType::class, 'synopsis/create.html.twig', $request);
    }

    /**
     * Edits a Synopsis
     */
    #[Route('/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(string $pk, Request $request): Response
    {
        
    }

}
