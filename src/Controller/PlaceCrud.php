<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Place;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Form\PlaceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * Creates a wildcard NPC from a template and a new name
     * @Route("/place/wildcard/{title}/{template}", methods={"GET"})
     */
    public function createWildcard(string $title, string $template): Response
    {
        $npc = $this->repository->findByTitle($template);
        if (is_null($npc) || (!$npc instanceof Transhuman)) {
            throw new NotFoundHttpException("$template does not exist");
        }
        /** @var Transhuman $wildcard */
        $wildcard = clone $npc;
        $wildcard->wildCard = true;
        $wildcard->setTitle($title);

        $this->repository->save($wildcard);

        return $this->redirectToRoute('app_npcgenerator_edit', ['pk' => $wildcard->getPk()]);
    }

}
