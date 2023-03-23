<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\BattlemapDocument;
use App\Entity\Place;
use App\Form\Tool3d\Battlemap3dWrite;
use App\Form\Tool3d\CubemapBroadcast;
use App\Form\Tool3d\RunningMap3dGui;
use App\Form\Tool3d\TileLegend;
use App\Repository\VertexRepository;
use App\Service\PlayerCastCache;
use App\Service\Storage;
use App\Service\WebsocketPusher;
use App\Voronoi\MapBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        // Toolbar with forms :
        $tools = $this->createForm(RunningMap3dGui::class);
        $broadcast = $this->createForm(CubemapBroadcast::class, null, [
            'action' => $this->generateUrl('app_firstperson_broadcast')
        ]);
        $legend = $this->createForm(TileLegend::class);
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
    public function babylon(Place $place, Storage $storage): Response
    {
        if (is_null($place->battlemap3d)) {
            $config = $place->voronoiParam;
            $map = $this->builder->create($config);
            $doc = new BattlemapDocument();
            $map->dumpMap($doc);

            $resp = new JsonResponse($doc);
            $resp->setLastModified(new \DateTime());

            return $resp;
        } else {
            return $storage->createResponse($place->battlemap3d);
        }
    }

    /**
     * @Route("/fps/broadcast", methods={"POST"})
     */
    public function broadcast(Request $request): JsonResponse
    {
        $form = $this->createForm(CubemapBroadcast::class);
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

            return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $target, 'imgType' => '3d']);
        }

        return new JsonResponse(['level' => 'error', 'message' => (string) $form->getErrors(true, true)], 400);
    }

    /**
     * The actual player screen updated with websocket
     * @Route("/player/fps", methods={"GET"})
     */
    public function player(): Response
    {
        $doc = new BattlemapDocument();
        (new \App\Voronoi\HexaMap(25))->dumpMap($doc);

        return $this->render('firstperson/player.html.twig', ['doc' => $doc, 'url_picture' => $this->pusher->getUrlCubemap()]);
    }

    /**
     * Delete the current battlemap
     * @Route("/fps/delete/{pk}", methods={"GET","DELETE"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function delete(Place $place, Request $request): Response
    {
        $form = $this->createFormBuilder($place)
                ->add('delete', SubmitType::class, ['attr' => ['class' => 'button-delete']])
                ->setMethod('DELETE')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $place->battlemap3d = null;
            $this->repository->save($place);
            $this->addFlash('success', 'La battlemap a été réinitialisée à sa version générée');

            return $this->redirectToRoute('app_firstperson_edit', ['pk' => $place->getPk()]);
        }

        return $this->render('firstperson/delete.html.twig', ['form' => $form->createView()]);
    }

}
