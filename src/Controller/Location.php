<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Form\LocationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Trismegiste\Toolbox\MongoDb\Repository;

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
        $obj = null;
        if ($request->query->has('title')) {
            $obj = new \App\Entity\Location();
            $obj->title = $request->query->get('title');
        }

        $form = $this->createForm(LocationType::class, $obj);

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

    /**
     * @Route("/wiki/{title}", methods={"GET"}, name="app_wiki")
     */
    public function wikiShow(string $title): Response
    {
        $it = $this->repository->search(['title' => $title]);
        $it->rewind();
        $loc = $it->current();
        if (is_null($loc)) {
            return $this->redirectToRoute('app_location_create', ['title' => $title]);
        }

        return $this->render('location/show.html.twig', ['location' => $loc]);
    }

    /**
     * @Route("/location/list", methods={"GET"})
     */
    public function list(): Response
    {
        return $this->render('location/list.html.twig', ['listing' => $this->repository->search()]);
    }

    /**
     * @Route("/location/edit/{pk}", methods={"GET","POST"})
     */
    public function edit(string $pk, Request $request): Response
    {
        $loc = $this->repository->load($pk);
        $form = $this->createForm(LocationType::class, $loc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $loc = $form->getData();
            $this->repository->save($loc);

            return $this->redirectToRoute('app_location_show', ['pk' => $loc->getPk()]);
        }

        return $this->render('form.html.twig', ['title' => 'Edit', 'form' => $form->createView()]);
    }

    /**
     * @Route("/location/delete/{pk}", methods={"GET","DELETE"})
     */
    public function delete(string $pk, Request $request): Response
    {
        $loc = $this->repository->load($pk);
        $form = $this->createFormBuilder($loc)
            ->add('delete', SubmitType::class)
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->delete($loc);

            return $this->redirectToRoute('app_location_list');
        }

        return $this->render('form.html.twig', ['title' => 'Delete', 'form' => $form->createView()]);
    }

}
