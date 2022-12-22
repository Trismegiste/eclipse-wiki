<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Babylon\Scene;
use App\Entity\Place;
use App\Voronoi\MapBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * 3D real time first personwith babylon.js
 */
class FirstPerson extends AbstractController
{

    protected MapBuilder $builder;

    public function __construct(MapBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @Route("/fps/view3d/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function view3d(Place $place): Response
    {
        return $this->render('firstperson/view3d.html.twig', ['place' => $place]);
    }

    /**
     * @Route("/fps/babylon/{pk}.{_format}", methods={"GET"}, requirements={"pk"="[\da-f]{24}", "_format": "battlemap"})
     */
    public function babylon(Place $place): JsonResponse
    {
        $config = $place->voronoiParam;
        $map = $this->builder->create($config);

        return new JsonResponse(new Scene($map));
    }

}
