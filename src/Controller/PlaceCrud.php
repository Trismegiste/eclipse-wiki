<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Place;
use App\Entity\Transhuman;
use App\Form\PlaceAppendMorphBank;
use App\Form\PlaceType;
use App\Parsoid\Parser;
use App\Service\DigraphExplore;
use App\Service\DocumentBroadcaster;
use App\Service\Mercure\Pusher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Place
 */
#[Route('/place')]
class PlaceCrud extends GenericCrud
{

    /**
     * Creates a Place
     */
    #[Route('/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate(PlaceType::class, 'place/create.html.twig', $request);
    }

    /**
     * Edits a Place
     */
    #[Route('/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(PlaceType::class, 'place/edit.html.twig', $pk, $request);
    }

    /**
     * Redirection to default NPC or Profile on the fly
     */
    #[Route('/npc/{title}', methods: ['GET'])]
    public function npcShow(string $title): Response
    {
        $npc = $this->repository->findByTitle($title);

        if (is_null($npc)) {
            throw new NotFoundHttpException("$title does not exist");
        }

        if (!$npc instanceof Transhuman) {
            $this->addFlash('error', "Cannot generate a profile from '$title' since it's not a transhuman");

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $npc->getPk()]);
        }

        if ($npc->isNpcTemplate()) {
            return $this->redirectToRoute('app_profilepicture_template', ['pk' => $npc->getPk()]);
        } else {
            $this->addFlash('error', "Cannot generate a profile from '$title' since it's not a NPC template");

            return $this->redirectToRoute('app_npcgenerator_info', ['pk' => $npc->getPk()]);
        }
    }

    protected function createPlaceChild(Place $place): Place
    {
        $title = $place->getTitle();
        $child = clone $place;
        $child->setTitle("Lieu enfant dans $title");
        $child->setContent("Localisé sur [[$title]]");
        $child->battlemap3d = null;
        $child->voronoiParam = null;

        return $child;
    }

    /**
     * Creates a Place child from the current Place
     */
    #[Route('/child/{pk}', methods: ['GET', 'POST'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function child(Place $place, Request $request): Response
    {
        $child = $this->createPlaceChild($place);
        $form = $this->createForm(PlaceType::class, $child);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('place/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Rendering of Places that are connected to this Place
     * @param Place $place
     * @param DigraphExplore $digraph
     * @return Response
     */
    public function connectionToPlace(Place $place, DigraphExplore $digraph): Response
    {
        return $this->render('fragment/place_connect.html.twig', ['connection' => $digraph->searchForConnectedPlace($place)]);
    }

    /**
     * Append a morph bank to the Place content
     */
    #[Route('/append-morph-bank/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function appendMorphBank(Place $place, Request $request): Response
    {
        $form = $this->createForm(PlaceAppendMorphBank::class, $place);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('place/morphbank/append.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Push a PDF with the content of the morph bank to the player public channel
     */
    #[Route('/push-morph-bank/{pk}', methods: ['POST'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pushMorphBank(Place $place, Request $request, DocumentBroadcaster $broadcast, Pusher $pusher, Parser $parsoid): JsonResponse
    {
        $data = $request->getPayload();
        if ($data->has('title')) {
            $title = $data->getString('title');
            $content = $parsoid->extractTagContent($place->getContent(), 'morphbank', $title);
            $html = $this->renderView('place/morphbank/inventory.pdf.twig', [
                'vertex' => $place,
                'filtered' => $content
            ]);
            $filename = "Banque-de-morphes-$title.pdf";
            $pdf = $broadcast->generatePdf($filename, $html);
            $link = $broadcast->getLinkToDocument($pdf->getBasename());
            $pusher->sendDocumentLink($link, "Banque de morphes $title");

            return new JsonResponse(['level' => 'success', 'message' => "$title envoyé"]);
        } else {
            $errorMessage = 'No title';
        }

        return new JsonResponse(['level' => 'error', 'message' => $errorMessage]);
    }

}
