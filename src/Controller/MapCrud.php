<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Form\ProceduralMap\OneBlockMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Exception\RuntimeException;

/**
 * Description of MapCrud
 */
class MapCrud extends AbstractController
{

    const model = [
        'oneblock' => OneBlockMap::class,
        'street' => \App\Form\ProceduralMap\StreetMap::class,
        'district' => \App\Form\ProceduralMap\DistrictMap::class
    ];

    /**
     * Show the creating form of a map
     * @Route("/map/{model}/create", methods={"GET"}, requirements={"model"="[a-z]+"})
     */
    public function mapCreate(string $model, Request $request): Response
    {
        $formClass = self::model[$model];
        $form = $this->createForm($formClass);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $map = $form->getData();

            $path = \join_paths($this->getUploadDir(), $form['map_name']->getData() . '.svg');
            $ptr = fopen($path, 'w');
            ob_start(function (string $buffer) use ($ptr) {
                fwrite($ptr, $buffer);
            });
            $map->printSvg();
            ob_end_clean();

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

}