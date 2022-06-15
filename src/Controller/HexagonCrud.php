<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\TileArrangement;
use App\Repository\TileProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD controler for Hexagonal map
 */
class HexagonCrud extends AbstractController
{

    protected $tileRepo;

    public function __construct(TileProvider $repo)
    {
        $this->tileRepo = $repo;
    }

    /**
     * @Route("/hex/create/set")
     */
    public function createSet(Request $request): Response
    {
        $form = $this->createForm(\App\Form\TileArrangementType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //  var_dump($form->getData());
        }

        return $this->render('hex/set_create.html.twig', ['form' => $form->createView()]);
    }

}
