<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Form\ProceduralMap\DistrictMap;
use App\Form\ProceduralMap\OneBlockMap;
use App\Form\ProceduralMap\SpaceshipMap;
use App\Form\ProceduralMap\StationMap;
use App\Form\ProceduralMap\StreetMap;
use App\Repository\MapRepository;
use App\Repository\VertexRepository;
use App\Service\WebsocketFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of MapCrud
 */
class MapCrud extends AbstractController
{

    const model = [
        'oneblock' => OneBlockMap::class,
        'street' => StreetMap::class,
        'district' => DistrictMap::class,
        'spaceship' => SpaceshipMap::class,
        'station' => StationMap::class
    ];

    protected $mapRepo;

    public function __construct(MapRepository $repo)
    {
        $this->mapRepo = $repo;
    }

    /**
     * Show the creating form of a map
     * @Route("/map/create/{model}", methods={"GET"}, requirements={"model"="[a-z]+"})
     */
    public function create(string $model, Request $request, VertexRepository $repo): Response
    {
        $formClass = self::model[$model];
        $data = null;
        if ($request->query->has('prefill')) {
            $data = $this->mapRepo->getTemplateParam($request->query->get('prefill'));
        }

        $form = $this->createForm($formClass, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $map = $form->getData();
            $place = $form['place']->getData();
            $newPlace = empty($place);

            if ($newPlace) {
                $place = new \App\Entity\Place('map-' . $form['seed']->getData());
                $repo->save($place);
            }

            $filename = 'map-' . $place->getPk() . '.svg';
            $this->mapRepo->writeAndSave($map, $filename, $place);
            $this->addFlash('success', 'Plan sauvegardé dans ' . $place->getTitle());

            return $this->redirectToRoute($newPlace ? 'app_vertexcrud_rename' : 'app_vertexcrud_show', ['pk' => $place->getPk()]);
        }

        return $this->render('map/form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Returns SVG
     * @Route("/map/generate/{model}", methods={"GET"}, requirements={"model"="[a-z]+"})
     */
    public function generate(string $model, Request $request): Response
    {
        $form = $this->createForm(self::model[$model]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $map = $form->getData();
            $response = new StreamedResponse(function () use ($map) {
                $map->printSvg();
            },
                Response::HTTP_CREATED,
                ['Content-Type' => 'image/svg+xml']
            );

            return $response;
        } else {
            throw new RuntimeException((string) $form->getErrors(true, true));
        }

        throw new RuntimeException('Invalid form');
    }

    /**
     * Show map in a popup
     * @Route("/map/popup/{model}", methods={"GET"}, requirements={"model"="[a-z]+"})
     */
    public function popup(string $model, Request $request): Response
    {
        $data = $request->query->all();
        $url = $this->generateUrl('app_mapcrud_generate', ['model' => $model]) . '?' . http_build_query($data);

        return $this->render('map/popup.html.twig', ['img' => $url]);
    }

    /**
     * Show listing of map templates
     * @Route("/map/list", methods={"GET"})
     */
    public function list(): Response
    {
        return $this->render('map/list.html.twig', ['template' => $this->mapRepo->findAll()]);
    }

    /**
     * AJAX Pushes the modified SVG to websocket server
     * @Route("/place/broadcast", methods={"POST"})
     */
    public function pushPlayerView(Request $request, \App\Service\WebsocketPusher $client): JsonResponse
    {
        $svgContent = $request->request->get('svg');
        $source = \join_paths($this->getParameter('kernel.cache_dir'), 'tmp-map.svg');
        file_put_contents($source, $svgContent);

        $target = \join_paths($this->getParameter('kernel.cache_dir'), 'tmp-map.png'); // @todo warmup cache dir
        $process = new Process([
            'wkhtmltoimage',
            '--quality', 50,
            '--crop-w', 800,
            $source,
            $target
        ]);
        $process->mustRun();

        try {
            $client->push(json_encode([
                'file' => $target,
                'title' => 'Toto'
            ]));

            return new JsonResponse(['level' => 'success', 'message' => 'Update pushed'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['level' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
