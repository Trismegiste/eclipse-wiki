<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\MediaWikiPage;
use App\Service\DocumentBroadcaster;
use App\Service\MediaWiki;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Description of FandomProxy
 *
 * @author florent
 */
#[Route('/fandom')]
class FandomProxy extends AbstractController
{

    public function __construct(protected MediaWiki $wiki)
    {
        
    }

    #[Route("/search", methods: ['GET'])]
    public function search(Request $request,): Response
    {
        $form = $this->createFormBuilder()
                ->add('query')
                ->add('search', SubmitType::class)
                ->setMethod('GET')
                ->getForm();

        $result = [];

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->wiki->searchPageByName($form['query']->getData());
        }

        return $this->render('fandom/search.html.twig', [
                    'title' => 'fandom',
                    'form' => $form->createView(),
                    'result' => $result
        ]);
    }

    #[Route("/show/{id}", methods: ['GET'])]
    public function show(int $id): Response
    {
        return $this->render('fandom/show.html.twig', [
                    'id' => $id,
                    'page' => $this->wiki->getWikitextById($id)
        ]);
    }

    #[Route("/pdf/{id}", methods: ['GET'])]
    public function pdf(int $id, DocumentBroadcaster $broadcast): BinaryFileResponse
    {
        $pdf = $this->generatePdf($this->wiki->getWikitextById($id), $broadcast);
        $resp = new BinaryFileResponse($pdf);
        $resp->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $pdf->getBasename());

        return $resp;
    }

    protected function generatePdf(MediaWikiPage $page, DocumentBroadcaster $broadcast): SplFileInfo
    {
        $title = sprintf("Aide-%s.pdf", $page->getTitle());
        $html = $this->renderView('fandom/export.pdf.twig', ['vertex' => $page]);

        return $broadcast->generatePdf($title, $html);
    }

    /**
     * Generates the PDF from fandom page and pushes to players
     */
    #[Route('/push/{id}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pushPdf(int $id, DocumentBroadcaster $broadcast): Response
    {
        $page = $this->wiki->getWikitextById($id);
        $pdf = $this->generatePdf($page, $broadcast);
        $this->addFlash('success', 'Aide Fandom générée');

        return $this->redirectToRoute('app_gmpusher_pushdocument', [
                    'pk' => $id,
                    'filename' => $pdf->getBasename(),
                    'label' => 'Aide - ' . $page->getTitle()
        ]);
    }

}
