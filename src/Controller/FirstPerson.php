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
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $screenshot */
        $screenshot = $request->files->get('picture');
        $cube = [];
        foreach ($screenshot as $idx => $pic) {
            list($w,$h) = getimagesize($pic->getPathname());
            // @todo need checks on size
            $cube[$idx] = imagecreatefrompng($pic->getPathname());
        }

        $cubemap = imagecreatetruecolor(4 * $w, 3 * $h);
        $target = join_paths($this->getParameter('kernel.cache_dir'), \App\Service\PlayerCastCache::subDir, 'tmp-cubemap.jpg');
        imagecopy($cubemap, $cube[0], 0, $h, 0, 0, $w, $h);
        imagecopy($cubemap, $cube[1], $w, $h, 0, 0, $w, $h);
        imagecopy($cubemap, $cube[2], 2*$w, $h, 0, 0, $w, $h);
        imagecopy($cubemap, $cube[3], 3*$w, $h, 0, 0, $w, $h);
        imagecopy($cubemap, $cube[4], 2*$w, 0, 0, 0, $w, $h);
        imagecopy($cubemap, $cube[5], 2*$w, 2*$h, 0, 0, $w, $h);
        imagejpeg($cubemap, $target);

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $target]);
    }

}
