<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Encounter;
use App\Entity\Vertex;
use App\Form\EncounterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Encounter
 */
class EncounterCrud extends GenericCrud
{

    protected function createEntity(string $title): Vertex
    {
        return new Encounter($title);
    }

    /**
     * Creates a Encounter
     * @Route("/encounter/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(EncounterType::class, 'encounter/create.html.twig', $request);
    }

    /**
     * Edits a Encounter
     * @Route("/encounter/edit/{pk}", methods={"GET","PUT"})
     */
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(EncounterType::class, 'encounter/edit.html.twig', $pk, $request);
    }

    /**
     * Generates the QR Code for the Encounter
     * @Route("/encounter/qrcode/{pk}", methods={"GET"})
     */
    public function qrCode(string $pk): Response
    {
        $vertex = $this->repository->findByPk($pk);

        return $this->render('encounter/qrcode.html.twig', ['vertex' => $vertex]);
    }

}
