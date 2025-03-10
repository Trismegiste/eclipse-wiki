<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Handout;
use App\Form\HandoutType;
use App\Repository\VertexRepository;
use App\Service\DocumentBroadcaster;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Handout
 */
#[Route('/handout')]
class HandoutCrud extends GenericCrud
{

    public function __construct(VertexRepository $repo)
    {
        parent::__construct($repo);
    }

    /**
     * Creates a Handout
     */
    #[Route('/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate(HandoutType::class, 'handout/create.html.twig', $request);
    }

    /**
     * Edits a Handout
     */
    #[Route('/edit/{pk}', methods: ['GET', 'PUT'])]
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(HandoutType::class, 'handout/edit.html.twig', $pk, $request);
    }

    /**
     * Generates the Handout PDF and push to players
     */
    #[Route('/push/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pushPdf(Handout $vertex, DocumentBroadcaster $broadcast): Response
    {
        $title = sprintf("Handout-%s.pdf", $vertex->getTitle());
        $html = $this->renderView('handout/pc_export.pdf.twig', ['vertex' => $vertex]);
        $pdf = $broadcast->generatePdf($title, $html);
        $this->addFlash('success', 'PDF Handout généré');

        return $this->redirectToRoute('app_gmpusher_pushdocument', [
                    'pk' => $vertex->getPk(),
                    'filename' => $pdf->getBasename(),
                    'label' => 'Aide de jeu - ' . $vertex->getTitle()
        ]);
    }

}
