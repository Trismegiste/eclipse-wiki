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
        'oneblock' => OneBlockMap::class
    ];

    /**
     * Show the creating form of a map
     * @Route("/map/{model}/create", methods={"GET"})
     */
    public function mapCreate(string $model, Request $request): Response
    {
        $formClass = self::model[$model];
        $form = $this->createForm($formClass);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $map = $form->getData();

            $ptr = fopen(__DIR__ . '/../../yolo.svg', 'w');
            ob_start(function (string $buffer) use ($ptr) {
                fwrite($ptr, $buffer);
            });
            $map->printSvg();
            ob_end_clean();

            $this->addFlash('success', 'Carte sauvegardée en ');
        }

        return $this->render('map/form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Returns SVG
     * @Route("/map/generate", methods={"GET"})
     */
    public function mapGenerate(Request $request): Response
    {
        $form = $this->createForm(OneBlockMap::class);

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
     * @Route("/map/popup", methods={"GET"})
     */
    public function popup(Request $request): Response
    {
        $data = $request->query->all();
        $url = $this->generateUrl('app_mapcrud_mapgenerate') . '?' . http_build_query($data);

        return $this->render('map/popup.html.twig', ['img' => $url]);
    }

}
