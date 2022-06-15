<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\TileArrangementType;
use App\Repository\TileArrangementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD controler for Hexagonal map
 */
class HexagonCrud extends AbstractController
{

    protected $tileRepo;

    public function __construct(TileArrangementRepository $repo)
    {
        $this->tileRepo = $repo;
    }

    /**
     * @Route("/hex/create/set")
     */
    public function createSet(Request $request): Response
    {
        $form = $this->createForm(TileArrangementType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->tileRepo->save($form->getData());
        }

        return $this->render('hex/set_create.html.twig', ['form' => $form->createView()]);
    }

}
