<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\GenerateMapForPlace;
use App\Form\MapConfigType;
use App\Form\MapTextureType;
use App\Repository\VertexRepository;
use App\Service\PlayerCastCache;
use App\Voronoi\MapBuilder;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use function join_paths;

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
     * Show the generated Voronoi map
     * @Route("/voronoi/storage/{pk}", methods={"GET","PATCH"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function storage(string $pk, Request $request): Response
    {
        $place = $this->repository->findByPk($pk);
        $form = $this->createForm(GenerateMapForPlace::class, $place);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);
            $this->addFlash('success', 'Battlemap générée et stockée');

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('voronoi/storage.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Creates or Edits a voronoi Map in the current Place
     * @Route("/voronoi/edit/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function edit(string $pk, Request $request): Response
    {
        $place = $this->repository->findByPk($pk);
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
     * @Route("/voronoi/generate/{pk}/{fog}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function generate(string $pk, bool $fog = true): Response
    {
        $place = $this->repository->load($pk);
        $config = $place->voronoiParam;

        try {
            $map = $this->builder->create($config);

            return new StreamedResponse(function () use ($map, $fog) {
                        $this->builder->dumpSvg($map, $fog);
                    }, Response::HTTP_OK, ['content-type' => 'image/svg+xml']);
        } catch (RuntimeException $e) {
            return new BinaryFileResponse($this->getParameter('twig.default_path') . '/voronoi/fail.svg', 200, [], false, null, false, false);
        }
    }

    /**
     * Edits tiles texturing of a map with direct view (loop)
     * @Route("/voronoi/texture/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function texture(string $pk, Request $request): Response
    {
        $place = $this->repository->load($pk);
        $form = $this->createFormBuilder($place)
                ->add('voronoiParam', MapTextureType::class, ['tileset' => 'habitat'])  // @todo hardcoded constant
                ->add('texture', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($form->getData());

            return $this->redirectToRoute('app_voronoicrud_texture', ['pk' => $pk]);
        }

        return $this->render('voronoi/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Show map to run it on the fly
     * @Route("/voronoi/runmap/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function runMap(string $pk): Response
    {
        $place = $this->repository->load($pk);
        $map = $this->builder->create($place->voronoiParam);
        ob_start();
        $this->builder->dumpSvg($map);
        $svg = ob_get_clean();

        return $this->render('place/runmap.html.twig', ['title' => 'Testing ' . $place->getTitle(), 'svg' => $svg]);
    }

    /**
     * @Route("/voronoi/statistics/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function statistics(string $pk): Response
    {
        $place = $this->repository->load($pk);
        $config = $place->voronoiParam;
        $map = $this->builder->create($config, false);

        return $this->render('voronoi/statistics.html.twig', ['vertex' => $place, 'stats' => $map->getStatistics()]);
    }

    /**
     * AJAX Pushes the modified SVG to websocket server
     * @Route("/voronoi/broadcast", methods={"POST"})
     */
    public function pushPlayerView(Request $request): JsonResponse
    {
        $playerDir = join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir);
        /** @var UploadedFile $svgContent */
        $svgContent = $request->files->get('svg')->move($playerDir, 'tmp-map.svg');
        // the moving was necessary because wkhtmltoimage fails to load a SVG file without extension
        $target = join_paths($playerDir, 'tmp-map.png');
        $process = new Process([
            'wkhtmltoimage',
            '--quality', 50,
            '--crop-w', MapBuilder::defaultSizeForWeb,
            $svgContent->getPathname(),
            $target
        ]);
        $process->mustRun();

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $target]);
    }

    /**
     * Edits tiles populations of a map
     * @Route("/voronoi/populate/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function populate(string $pk, Request $request): Response
    {
        $place = $this->repository->load($pk);
        $form = $this->createFormBuilder($place)
                ->add('voronoiParam', \App\Form\MapPopulationType::class)
                ->add('populate', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($form->getData());

            return $this->redirectToRoute('app_voronoicrud_populate', ['pk' => $pk]);
        }

        return $this->render('voronoi/edit.html.twig', ['form' => $form->createView()]);
    }

}
