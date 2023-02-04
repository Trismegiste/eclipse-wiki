<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Babylon\Scene;
use App\Entity\Place;
use App\Form\RunningMap3dGui;
use App\Service\PlayerCastCache;
use App\Service\WebsocketPusher;
use App\Voronoi\MapBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function join_paths;

/**
 * 3D real time first personwith babylon.js
 */
class FirstPerson extends AbstractController
{

    protected MapBuilder $builder;
    protected $pusher;

    public function __construct(MapBuilder $builder, WebsocketPusher $fac)
    {
        $this->builder = $builder;
        $this->pusher = $fac;
    }

    /**
     * @Route("/fps/explore/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function explore(Place $place): Response
    {
        $tools = $this->createForm(RunningMap3dGui::class);
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
    public function publish(Request $request): JsonResponse
    {
        /** @var UploadedFile $screenshot */
        $screenshot = $request->files->get('picture');
        $cube = [];
        foreach ($screenshot as $idx => $pic) {
            list($w, $h) = getimagesize($pic->getPathname());
            // @todo need checks on size
            $cube[$idx] = imagecreatefrompng($pic->getPathname());
        }

        $cubemap = imagecreatetruecolor(6 * $w, $h);
        $target = join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir, 'tmp-cubemap.jpg');
        for ($k = 0; $k < 6; $k++) {
            imagecopy($cubemap, $cube[$k], $k * $w, 0, 0, 0, $w, $h);
        }
        imagejpeg($cubemap, $target);

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $target]);
    }

    /**
     * The actual player screen updated with websocket
     * @Route("/fps/player", methods={"GET"})
     */
    public function player(): Response
    {
        return $this->render('firstperson/player.html.twig', ['host' => $this->pusher->getUrl()]);
    }

}
