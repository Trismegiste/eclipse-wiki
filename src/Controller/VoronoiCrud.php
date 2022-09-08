<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\MapConfig;
use App\Entity\Place;
use App\Entity\Vertex;
use App\Form\GenerateMapForPlace;
use App\Form\MapConfigType;
use App\Form\MapTextureType;
use App\Repository\VertexRepository;
use App\Service\PlayerCastCache;
use App\Service\Storage;
use App\Voronoi\MapBuilder;
use RuntimeException;
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
class VoronoiCrud extends GenericCrud
{

    protected MapBuilder $builder;

    public function __construct(VertexRepository $repo, MapBuilder $builder)
    {
        parent::__construct($repo);
        $this->builder = $builder;
    }

    /**
     * @Route("/voronoi/generate/{pk}/{fog}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function generate(string $pk, bool $fog = true): Response
    {
        $config = $this->repository->load($pk);

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
     * Creates a battlemap config
     * @Route("/voronoi/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(MapConfigType::class, 'voronoi/create.html.twig', $request);
    }

    /**
     * Edits a battlemap config with direct view (loop)
     * @Route("/voronoi/edit/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function edit(string $pk, Request $request): Response
    {
        $resp = $this->handleEdit(MapConfigType::class, 'voronoi/edit.html.twig', $pk, $request);
        if ($resp->isRedirection()) {
            return $this->redirectToRoute('app_voronoicrud_edit', ['pk' => $pk]);
        }

        return $resp;
    }

    protected function createEntity(string $title): Vertex
    {
        return new MapConfig($title);
    }

    /**
     * Show map to run it on the fly
     * @Route("/voronoi/running/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function running(string $pk): Response
    {
        $config = $this->repository->load($pk);
        $map = $this->builder->create($config);
        ob_start();
        $this->builder->dumpSvg($map);
        $svg = ob_get_clean();

        return $this->render('map/running.html.twig', ['title' => 'On the fly ' . $config->getTitle(), 'svg' => $svg]);
    }

    /**
     * Attach the generated map to a Place entity
     * @Route("/voronoi/attachplace/{pk}", methods={"GET","PATCH"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function attachPlace(string $pk, Request $request, Storage $storage): Response
    {
        /** @var MapConfig $config */
        $config = $this->repository->load($pk);

        $form = $this->createForm(GenerateMapForPlace::class, null, ['map_config' => $config]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // getting place
            $place = $form['place']->getData();
            $newPlace = empty($place);
            if ($newPlace) {
                $place = new Place($form['default_newname']->getData());
                $this->repository->save($place);
            }

            // attach generated map to the place
            $filename = 'map-' . $place->getPk() . '.svg';
            $this->builder->save($form->getData(), join_paths($storage->getRootDir(), $filename));
            $place->battleMap = $filename;
            $this->repository->save($place);

            $this->addFlash('success', 'Plan sauvegardé dans ' . $place->getTitle());

            return $this->redirectToRoute($newPlace ? 'app_vertexcrud_rename' : 'app_vertexcrud_show', ['pk' => $place->getPk()]);
        }

        return $this->render('voronoi/attachplace.html.twig', ['vertex' => $config, 'form' => $form->createView()]);
    }

    /**
     * Edits tiles texturing of a map with direct view (loop)
     * @Route("/voronoi/texture/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function texture(string $pk, Request $request): Response
    {
        $config = $this->repository->load($pk);
        $form = $this->createForm(MapTextureType::class, $config, ['tileset' => 'habitat']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($form->getData());

            return $this->redirectToRoute('app_voronoicrud_texture', ['pk' => $pk]);
        }

        return $this->render('voronoi/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/voronoi/statistics/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function statistics(string $pk): Response
    {
        $config = $this->repository->load($pk);
        $map = $this->builder->create($config, false);

        return $this->render('voronoi/statistics.html.twig', ['vertex' => $config, 'stats' => $map->getStatistics()]);
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

}
