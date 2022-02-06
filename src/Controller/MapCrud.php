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

    /**
     * Show the creating form of a map
     * @Route("/map/create", methods={"GET"})
     */
    public function mapCreate(Request $request): Response
    {
        $form = $this->createForm(OneBlockMap::class);

        return $this->render('place/map/form.html.twig', ['form' => $form->createView()]);
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

}
