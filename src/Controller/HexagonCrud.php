<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Repository\TileProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function createSet(): Response
    {
        return $this->render('hex/create_set.html.twig', ['listing' => $this->tileRepo->findAll()]);
    }

}
