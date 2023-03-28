<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\BattlemapDocument;
use App\Entity\Place;
use App\Form\MapConfigType;
use App\Form\MapPopulationType;
use App\Form\MapTextureType;
use App\Repository\VertexRepository;
use App\Voronoi\MapBuilder;
use App\Voronoi\SvgDumper;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD controler for Hexagonal map
 */
class VoronoiCrud extends AbstractController
{

    protected MapBuilder $builder;
    protected VertexRepository $repository;

    public function __construct(VertexRepository $repo, MapBuilder $builder)
    {
        $this->builder = $builder;
        $this->repository = $repo;
    }

    /**
     * Creates or Edits a voronoi Map in the current Place
     */
    #[Route('/voronoi/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(Place $place, Request $request): Response
    {
        $form = $this->createFormBuilder($place)
                ->add('voronoiParam', MapConfigType::class)
                ->add('generate', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_voronoicrud_edit', ['pk' => $place->getPk()]);
        }

        return $this->render('voronoi/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     */
    #[Route('/voronoi/generate/{pk}/{fog}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function generate(SvgDumper $dumper, Place $place, bool $fog = true): Response
    {
        $config = $place->voronoiParam;

        try {
            $map = $this->builder->create($config);
            $doc = new BattlemapDocument();
            $map->dumpMap($doc);

            return new StreamedResponse(function () use ($doc, $dumper) {
                        $dumper->flush($doc);
                    }, Response::HTTP_OK, ['content-type' => 'image/svg+xml']);
        } catch (RuntimeException $e) {
            return new BinaryFileResponse($this->getParameter('twig.default_path') . '/voronoi/fail.svg', 200, [], false, null, false, false);
        }
    }

    /**
     * Edits tiles texturing of a map with direct view (loop)
     * {tileset} is here for future tileset one day
     */
    #[Route('/voronoi/texture/{pk}/{tileset}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function texture(Place $place, Request $request, $tileset = 'habitat'): Response
    {
        $form = $this->createFormBuilder($place)
                ->add('voronoiParam', MapTextureType::class, ['tileset' => $tileset])
                ->add('texture', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($form->getData());

            return $this->redirectToRoute('app_voronoicrud_texture', ['pk' => $place->getPk()]);
        }

        return $this->render('voronoi/texture.html.twig', ['form' => $form->createView()]);
    }

    /**
     */
    #[Route('/voronoi/statistics/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function statistics(Place $place): Response
    {
        $config = $place->voronoiParam;
        $map = $this->builder->create($config, false);

        return $this->render('voronoi/statistics.html.twig', ['vertex' => $place, 'stats' => $map->getStatistics()]);
    }

    /**
     * Edits tiles populations of a map
     */
    #[Route('/voronoi/populate/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function populate(Place $place, Request $request): Response
    {
        $form = $this->createFormBuilder($place)
                ->add('voronoiParam', MapPopulationType::class)
                ->add('populate', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($form->getData());

            return $this->redirectToRoute('app_voronoicrud_populate', ['pk' => $place->getPk()]);
        }

        return $this->render('voronoi/populate.html.twig', ['form' => $form->createView()]);
    }

}
