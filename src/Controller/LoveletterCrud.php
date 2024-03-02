<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Loveletter;
use App\Entity\Vertex;
use App\Form\LoveletterPcChoice;
use App\Form\LoveletterType;
use App\Service\DocumentBroadcaster;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Loveletter
 */
#[Route('/loveletter')]
class LoveletterCrud extends GenericCrud
{

    protected function createEntity(string $title): Vertex
    {
        return new Loveletter($title);
    }

    /**
     * Creates a Love letter
     */
    #[Route('/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate(LoveletterType::class, 'loveletter/create.html.twig', $request);
    }

    /**
     * Edits a Love letter
     */
    #[Route('/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(LoveletterType::class, 'loveletter/edit.html.twig', $pk, $request);
    }

    /**
     * Generate PDF for a Love letter
     */
    #[Route('/pdf/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pdf(Loveletter $vertex, DocumentBroadcaster $broadcast): BinaryFileResponse
    {
        $pdf = $this->generatePdf($vertex, $broadcast);
        $resp = new BinaryFileResponse($pdf);
        $resp->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $pdf->getBasename());

        return $resp;
    }

    /**
     * Generates the Love letter PDF and push to players
     */
    #[Route('/push/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pushPdf(Loveletter $vertex, DocumentBroadcaster $broadcast): Response
    {
        $pdf = $this->generatePdf($vertex, $broadcast);
        $this->addFlash('success', 'PDF Loveletter généré');

        return $this->redirectToRoute('app_gmpusher_pushdocument', [
                    'pk' => $vertex->getPk(),
                    'filename' => $pdf->getBasename(),
                    'label' => 'Loveletter - ' . $vertex->getTitle()
        ]);
    }

    /**
     * Selection of the PC for the different resolution of the love letter
     */
    #[Route('/select/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function select(string $pk, Request $request): Response
    {
        return $this->handleEdit(LoveletterPcChoice::class, 'loveletter/select.html.twig', $pk, $request);
    }

    protected function generatePdf(Loveletter $vertex, DocumentBroadcaster $broadcast): SplFileInfo
    {
        $title = sprintf("Loveletter-%s-%s.pdf", $vertex->player, $vertex->getTitle());
        $html = $this->renderView('loveletter/export.pdf.twig', ['vertex' => $vertex]);

        return $broadcast->generatePdf($title, $html);
    }

}
