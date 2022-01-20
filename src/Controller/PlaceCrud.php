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
use Trismegiste\NameGenerator\FileRepository;
use Trismegiste\NameGenerator\RandomizerDecorator;

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
     * Easy generation NPC on a Place
     * @Route("/place/npc/{pk}", methods={"GET","POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function generateNpc(string $pk, Request $request): Response
    {
        $repo = new RandomizerDecorator(new FileRepository());
        $vertex = $this->repository->findByPk($pk);
        $card = 16;

        $listing = [];
        foreach (['female', 'male'] as $gender) {
            for ($k = 0; $k < $card; $k++) {
                $lang = random_int(0, 100) < 75 ? $vertex->surnameLang : 'random';
                $listing[$gender][] = $repo->getRandomGivenNameFor($gender, 'random') . ' ' . $repo->getRandomSurnameFor($lang);
            }
        }

        return $this->render('place/npc_generate.html.twig', ['place' => $vertex, 'listing' => $listing]);
    }

}
