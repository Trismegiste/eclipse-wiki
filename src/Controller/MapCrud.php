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
use App\MapLayer\IteratorDecorator;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use function join_paths;

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

    /**
     * Show the creating form of a map
     * @Route("/map/{model}/create", methods={"GET"}, requirements={"model"="[a-z]+"})
     */
    public function mapCreate(string $model, Request $request, VertexRepository $repo): Response
    {
        $formClass = self::model[$model];
        $form = $this->createForm($formClass);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $map = $form->getData();

            $place = $form['place']->getData();
            $filename = empty($place) ? 'map-' . $form['seed']->getData() : 'map-' . $place->getPk();
            $filename .= '.svg';
            $path = \join_paths($this->getUploadDir(), $filename);

            $ptr = fopen($path, 'w');
            ob_start(function (string $buffer) use ($ptr) {
                fwrite($ptr, $buffer);
            });
            $map->printSvg();
            ob_end_clean();

            if (!empty($place)) {
                $place->battleMap = $filename;
                $repo->save($place);
            }

            $this->addFlash('success', 'Plan sauvegardé en ' . $path);
        }

        return $this->render('map/form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Returns SVG
     * @Route("/map/{model}/generate", methods={"GET"}, requirements={"model"="[a-z]+"})
     */
    public function mapGenerate(string $model, Request $request): Response
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
        }

        throw new RuntimeException('Invalid form');
    }

    /**
     * Show map in a popup
     * @Route("/map/{model}/popup", methods={"GET"}, requirements={"model"="[a-z]+"})
     */
    public function popup(string $model, Request $request): Response
    {
        $data = $request->query->all();
        $url = $this->generateUrl('app_mapcrud_mapgenerate', ['model' => $model]) . '?' . http_build_query($data);

        return $this->render('map/popup.html.twig', ['img' => $url]);
    }

    protected function getUploadDir(): string
    {
        return \join_paths($this->getParameter('kernel.project_dir'), 'public/upload');
    }

    /**
     * Show listing of map templates
     * @Route("/map/list", methods={"GET"})
     */
    public function list(): Response
    {
        $template = new Finder();
        $it = $template->in(join_paths($this->getParameter('kernel.project_dir'), 'public/map'))
                ->files()
                ->name('*.svg')
                ->getIterator();

        return $this->render('map/list.html.twig', ['template' => new IteratorDecorator($it)]);
    }

}
