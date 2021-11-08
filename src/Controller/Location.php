<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Form\LocationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Trismegiste\Toolbox\MongoDb\Repository;
use Wikimedia\LittleWikitext\LittleWikitext;

/**
 * CRUD for Location
 */
class Location extends AbstractController
{

    protected $repository;

    public function __construct(Repository $documentRepo)
    {
        $this->repository = $documentRepo;
    }

    /**
     * @Route("/location/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(LocationType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $loc = $form->getData();
            $this->repository->save($loc);

            return $this->redirectToRoute('app_location_show', ['pk' => $loc->getPk()]);
        }

        return $this->render('form.html.twig', ['title' => 'Lieu', 'form' => $form->createView()]);
    }

    /**
     * @Route("/location/show/{pk}", methods={"GET"})
     */
    public function show(string $pk): Response
    {
        $loc = $this->repository->load($pk);

        return $this->render('location/show.html.twig', ['location' => $loc]);
    }

}
