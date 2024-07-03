<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\BattlemapDocument;
use App\Entity\Place;
use App\Form\Tool3d\Battlemap3dWrite;
use App\Form\Tool3d\CubemapBroadcast;
use App\Form\Tool3d\GmViewBroadcast;
use App\Form\Tool3d\RoomTexturing;
use App\Form\Tool3d\SpotSelect;
use App\Form\Tool3d\TileLegend;
use App\Form\Tool3d\TileNpc;
use App\Repository\VertexRepository;
use App\Service\Storage;
use App\Voronoi\MapBuilder;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * 3D real time first personwith babylon.js
 */
class FirstPerson extends AbstractController
{

    protected MapBuilder $builder;
    protected VertexRepository $repository;

    public function __construct(VertexRepository $repo, MapBuilder $builder)
    {
        $this->builder = $builder;
        $this->repository = $repo;
    }

    /**
     * Runs and edits the map in the GM view
     */
    #[Route('/fps/edit/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(Place $place): Response
    {
        // Toolbar with forms :
        $npcTool = $this->createForm(TileNpc::class);
        $broadcast = $this->createForm(CubemapBroadcast::class, null, [
            'action' => $this->generateUrl('app_firstperson_broadcast')
        ]);
        $gmView = $this->createForm(GmViewBroadcast::class, null, [
            'action' => $this->generateUrl('app_firstperson_pushgmview')
        ]);
        $legend = $this->createForm(TileLegend::class);
        $texturing = $this->createForm(RoomTexturing::class);
        $writer = $this->createForm(Battlemap3dWrite::class, $place, [
            'action' => $this->generateUrl('app_firstperson_export', ['pk' => $place->getPk()])
        ]);
        $spot = $this->createForm(SpotSelect::class, null, ['place' => $place]);

        return $this->render('firstperson/edit.html.twig', [
                    'place' => $place,
                    'npc_tool' => $npcTool->createView(),
                    'legend' => $legend->createView(),
                    'texturing' => $texturing->createView(),
                    'writer' => $writer->createView(),
                    'broadcast' => $broadcast->createView(),
                    'gm_view' => $gmView->createView(),
                    'spot' => $spot->createView()
        ]);
    }

    /**
     * Saves the battlemap into JSON
     */
    #[Route('/fps/export/{pk}', methods: ['PATCH'], requirements: ['pk' => '[\\da-f]{24}'])]
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
     * Gets the battlemap for a Place
     * If it already exists, it sends back, or it generates it with voronoi algorithm
     */
    #[Route('/fps/scene/{pk}.{_format}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}', '_format' => 'battlemap'])]
    public function babylon(Place $place, Storage $storage, Request $request): Response
    {
        if (is_null($place->battlemap3d)) {
            $config = $place->voronoiParam;
            $map = $this->builder->create($config);
            $doc = new BattlemapDocument();
            $map->dumpMap($doc);

            $resp = new JsonResponse($doc);
            $resp->setLastModified(new DateTime());
        } else {
            $resp = $storage->createResponse($place->battlemap3d);
            $resp->isNotModified($request);
        }
        $resp->setCache(['must_revalidate' => true]);

        return $resp;
    }

    /**
     * Broadcast the cubemap view by receiving the 6 camera pictures
     */
    #[Route('/fps/broadcast', methods: ['POST'])]
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

            return $this->forward(GmPusher::class . '::internalPushPicture', [
                        'label' => 'cubemap 3d',
                        'picture' => $cubemap,
                        'imgType' => 'cubemap'
            ]);
        }

        return new JsonResponse(['level' => 'error', 'message' => (string) $form->getErrors(true, true)], 400);
    }

    /**
     * Delete the current battlemap
     */
    #[Route('/fps/delete/{pk}', methods: ['GET', 'DELETE'], requirements: ['pk' => '[\\da-f]{24}'])]
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

    /**
     * Broadcast the GM view 2d screenshot
     */
    #[Route('/fps/push/gmview', methods: ['POST'])]
    public function pushGmView(Request $request): JsonResponse
    {
        $form = $this->createForm(GmViewBroadcast::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $screenshot */
            $screenshot = $form['picture']->getData();
            $target = imagecreatefromstring($screenshot->getContent());

            return $this->forward(GmPusher::class . '::internalPushPicture', [
                        'label' => $screenshot->getBasename(),
                        'picture' => $target,
                        'imgType' => 'battlemap'
            ]);
        }

        return new JsonResponse(['level' => 'error', 'message' => (string) $form->getErrors(true, true)], 400);
    }

}
