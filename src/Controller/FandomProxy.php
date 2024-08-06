<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\MediaWikiPage;
use App\Form\FandomSearch;
use App\Form\Type\TopicSelectorType;
use App\Service\DocumentBroadcaster;
use App\Service\MediaWiki;
use App\Service\Mercure\Pusher;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Search against the fandom mediawiki (pictures and articles)
 */
#[Route('/fandom')]
class FandomProxy extends AbstractController
{

    public function __construct(protected MediaWiki $wiki)
    {
        
    }

    /**
     * Search items in the fandom mediawiki
     * @param Request $request
     * @return Response
     */
    #[Route("/search", methods: ['GET'])]
    public function search(Request $request): Response
    {
        $form = $this->createForm(FandomSearch::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $form->getData();
        }

        if (isset($result)) {
            $resp = $this->render('fandom/' . $result['namespace']->getTemplateName(), [
                'form' => $form->createView(),
                'result' => $result['listing']
            ]);
        } else {
            $resp = $this->render("fandom/search.html.twig", [
                'form' => $form->createView()
            ]);
        }

        return $resp;
    }

    /**
     * Show a remote (from the fandom) wikitext content with the local rendering engine (after some cleaning)
     * Note : does not manage remote template. Experimental
     * @param int $id the page id from the mediawiki
     * @return Response
     */
    #[Route("/show/{id}", methods: ['GET'])]
    public function show(int $id): Response
    {
        return $this->render('fandom/show.html.twig', [
                    'id' => $id,
                    'page' => $this->wiki->getWikitextById($id)
        ]);
    }

    /**
     * Same as show but generates a PDF
     * @param int $id The page id on the remote MediaWiki
     * @param DocumentBroadcaster $broadcast
     * @return BinaryFileResponse
     */
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
     * Generates the PDF from fandom page and pushes to players (public channel since it's a public mediawiki)
     * @param int $id The page id from MediaWiki
     * @param DocumentBroadcaster $broadcast
     * @param Pusher $pusher
     * @return Response
     */
    #[Route('/push/{id}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pushPdf(int $id, DocumentBroadcaster $broadcast, Pusher $pusher): Response
    {
        // @todo wikipage need to be cached
        $page = $this->wiki->getWikitextById($id);
        $pdf = $this->generatePdf($page, $broadcast);
        $url = $broadcast->getLinkToDocument($pdf->getBasename());
        $pusher->sendDocumentLink($url, $page->getTitle(), TopicSelectorType::PUBLIC_CHANNEL);
        $this->addFlash('success', 'Aide Fandom envoyée');

        return $this->redirectToRoute('app_fandomproxy_show', ['id' => $id]);
    }

    /**
     * Ajax search for autocomplete with fandom content
     * @param Request $request
     * @return JsonResponse
     */
    #[Route("/autocomplete", methods: ['GET'])]
    public function autocomplete(Request $request): JsonResponse
    {
        $title = $request->query->get('q', '');
        return new JsonResponse(array_column($this->wiki->prefixSearch($title), 'title'));
    }

}
