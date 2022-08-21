<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Place;
use App\Entity\Vertex;
use App\Form\PlaceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Place
 */
class PlaceCrud extends GenericCrud
{

    /**
     * Creates a Place
     * @Route("/place/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(PlaceType::class, 'place/create.html.twig', $request);
    }

    /**
     * Edits a Place
     * @Route("/place/edit/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(PlaceType::class, 'place/edit.html.twig', $pk, $request);
    }

    protected function createEntity(string $title): Vertex
    {
        return new Place($title);
    }

    /**
     * Page for the battlemap
     * @Route("/place/battlemap/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function battlemap(string $pk): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $url = $this->generateUrl('get_picture', ['title' => $vertex->battleMap]);

        return $this->render('map/running.html.twig', ['title' => $vertex->getTitle(), 'img' => $url]);
    }

    /**
     * Redirection to default NPC or Profile on the fly
     * @Route("/place/npc/{title}", methods={"GET"})
     */
    public function npcShow(string $title): Response
    {
        $npc = $this->repository->findByTitle($title);

        if (is_null($npc) || (!$npc instanceof \App\Entity\Transhuman)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("$title is not a Transhuman");
        }

        if (!empty($npc->surnameLang)) {
            return $this->redirectToRoute('app_profilepicture_generateonthefly', ['pk' => $npc->getPk()]);
        } else {
            $this->addFlash('error', "Cannot generate a profile from '$title' since its surname language is not defined");

            return $this->redirectToRoute('app_npcgenerator_info', ['pk' => $npc->getPk()]);
        }
    }

    /**
     * Creates a Place child from the current Place
     * @Route("/place/child/{pk}", methods={"GET","POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function child(string $pk, Request $request): Response
    {
        $place = $this->repository->findByPk($pk);
        if (is_null($place) || (!$place instanceof \App\Entity\Place)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Vertex $pk is not a Place");
        }

        $title = $place->getTitle();
        $child = clone $place;
        $child->setTitle("Lieu enfant dans $title");
        $child->setContent("Localisé sur [[$title]]");
        $form = $this->createForm(PlaceType::class, $child);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('place/create.html.twig', ['form' => $form->createView()]);
    }

}
