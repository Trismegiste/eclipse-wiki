<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Babylon\Scene;
use App\Entity\Place;
use App\Form\Battlemap3dWrite;
use App\Form\RunningMap3dGui;
use App\Repository\VertexRepository;
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
    protected VertexRepository $repository;

    public function __construct(VertexRepository $repo, MapBuilder $builder, WebsocketPusher $fac)
    {
        $this->builder = $builder;
        $this->pusher = $fac;
        $this->repository = $repo;
    }

    /**
     * @Route("/fps/edit/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function edit(Place $place): Response
    {
        $tools = $this->createForm(RunningMap3dGui::class);
        $broadcast = $this->createForm(\App\Form\CubemapBroadcast::class, null, [
            'action' => $this->generateUrl('app_firstperson_broadcast')
        ]);

        $legend = $this->createFormBuilder()
                ->add('legend', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class)
                ->add('name', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
                ->getForm();

        $writer = $this->createForm(Battlemap3dWrite::class, $place, [
            'action' => $this->generateUrl('app_firstperson_export', ['pk' => $place->getPk()])
        ]);

        return $this->render('firstperson/edit.html.twig', [
                    'place' => $place,
                    'tools' => $tools->createView(),
                    'legend' => $legend->createView(),
                    'writer' => $writer->createView(),
                    'broadcast' => $broadcast->createView()
        ]);
    }

    /**
     * @Route("/fps/export/{pk}", methods={"PATCH"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function export(Place $place, Request $request): JsonResponse
    {
        $writer = $this->createForm(Battlemap3dWrite::class, $place);
        $writer->handleRequest($request);
        if ($writer->isSubmitted() && $writer->isValid()) {
            $vertex = $writer->getData();
            $this->repository->save($vertex);

            return new JsonResponse(['level' => 'success', 'message' => 'Saved']);
        }

        return new JsonResponse(['level' => 'error', 'message' => (string) $writer->getErrors(true, true)], 400);
    }

    /**
     * @Route("/fps/scene/{pk}.{_format}", methods={"GET"}, requirements={"pk"="[\da-f]{24}", "_format": "battlemap"})
     */
    public function babylon(Place $place): JsonResponse
    {
        $config = $place->voronoiParam;
        $map = $this->builder->create($config);

        return new JsonResponse(new Scene($map));
    }

    /**
     * @Route("/fps/broadcast", methods={"POST"})
     */
    public function broadcast(Request $request): JsonResponse
    {
        $form = $this->createForm(\App\Form\CubemapBroadcast::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $screenshot */
            $screenshot = $form['picture']->getData();
            list($w, $h) = getimagesize($screenshot[0]->getPathname());
            $cubemap = imagecreatetruecolor(6 * $w, $h);

            for ($k = 0; $k < 6; $k++) {
                $side = imagecreatefrompng($screenshot[$k]->getPathname());
                imagecopy($cubemap, $side, $k * $w, 0, 0, 0, $w, $h);
                imagedestroy($side);
            }
            $target = join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir, 'tmp-cubemap.jpg');
            imagejpeg($cubemap, $target);
            imagedestroy($cubemap);

            return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $target]);
        }

        return new JsonResponse(['level' => 'error', 'message' => (string) $form->getErrors(true, true)], 400);
    }

    /**
     * The actual player screen updated with websocket
     * @Route("/player/fps", methods={"GET"})
     */
    public function player(): Response
    {
        return $this->render('firstperson/player.html.twig', ['host' => $this->pusher->getUrl()]);
    }

}
