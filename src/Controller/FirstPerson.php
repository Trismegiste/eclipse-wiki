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
     * @Route("/fps/explore/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function explore(Place $place): Response
    {
        $tools = $this->createForm(\App\Form\RunningMap3dGui::class);
        return $this->render('firstperson/view3d.html.twig', ['place' => $place, 'tools' => $tools->createView()]);
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

    /**
     * @Route("/fps/publish", methods={"POST"})
     */
    public function publish(\Symfony\Component\HttpFoundation\Request $request): JsonResponse
    {
        $playerDir = join_paths($this->getParameter('kernel.cache_dir'), \App\Service\PlayerCastCache::subDir);
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $screenshot */
        $screenshot = $request->files->get('picture');
        foreach ($screenshot as $idx => $pic) {
            $pic->move($playerDir, "tmp-map-$idx.png");
        }

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $screenshot[5]->getPathname()]);
    }

}
