<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Form\GallerySelection;
use App\Service\GameSessionTracker;
use App\Service\Pdf\ChromiumPdfWriter;
use App\Service\SessionPushHistory;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use function join_paths;

/**
 * Tracking actions for the current Game Session
 */
#[Route('/session')]
class GameSession extends AbstractController
{

    public function __construct(protected SessionPushHistory $broadcastHistory)
    {
        
    }

    /**
     * Shows the history of the GM
     * @param GameSessionTracker $tracker
     * @return Response
     */
    public function history(GameSessionTracker $tracker): Response
    {
        return $this->render('gamesession/history.html.twig', ['document' => $tracker->getDocument()]);
    }

    #[Route('/broadcast-export', methods: ['GET', 'POST'])]
    public function broadcastExport(Request $request, ChromiumPdfWriter $pdf): Response
    {
        $form = $this->createForm(GallerySelection::class, $this->broadcastHistory->getListing());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filename = 'Rapport-' . date('d-m-Y') . '.pdf';
            $target = new SplFileInfo(join_paths($this->getParameter('kernel.cache_dir'), $filename));
            $pdf->renderToPdf('gamesession/report.pdf.twig', ['listing' => $form->getData()], $target);

            $resp = new BinaryFileResponse($target);
            $resp->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

            return $resp;
        }

        return $this->render('gamesession/broadcasted.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Show broadcasted picture from cache
     */
    #[Route('/broadcasted-picture/{title}', methods: ['GET'])]
    public function broadcasted(string $title): Response
    {
        return $this->broadcastHistory->createResponse($title);
    }

}
