<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Place;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Form\PlaceAppendMorphBank;
use App\Form\PlaceType;
use App\Service\DigraphExplore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    protected function createEntity(string $title): Vertex
    {
        return new Place($title);
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

    /**
     * Creates a Place child from the current Place
     */
    #[Route('/child/{pk}', methods: ['GET', 'POST'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function child(Place $place, Request $request): Response
    {
        $title = $place->getTitle();
        $child = clone $place;
        $child->setTitle("Lieu enfant dans $title");
        $child->setContent("Localisé sur [[$title]]");
        $child->battlemap3d = null;
        $child->voronoiParam = null;
        // @todo faire qqc pour cette initialisation => methode __clone ? Dans la factory des Vertex ?
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

        return $this->render('place/morph_bank.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Push a PDF with the content of the morph bank to the player public channel
     */
    #[Route('/push-morph-bank/{pk}', methods: ['POST'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pushMorphBank(Place $place, Request $request): JsonResponse
    {
        $data = $request->getPayload();
        if (!$data->has('title')) {
	    $title = $data->getString('title');
            // @todo generate PDF from the fragment of HTML
        } else {
            $errorMessage = 'No title';
        }

        return new JsonResponse(['level' => 'error', 'message' => $errorMessage]);
    }

}
