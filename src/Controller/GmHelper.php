<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Ali;
use App\Entity\Freeform;
use App\Entity\Transhuman;
use App\Repository\VertexRepository;
use App\Service\DigraphExplore;
use App\Service\InfoDashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Trismegiste\NameGenerator\FileRepository;
use Trismegiste\NameGenerator\RandomizerDecorator;

/**
 * Quick generator and helper for the GM
 */
class GmHelper extends AbstractController
{

    /**
     * Names generator. Demographic specifications are chosen in the global parameter "generator"
     */
    #[Route("/gm/name/{card}", methods: ["GET"])]
    public function nameGenerate(int $card = 15): Response
    {
        $repo = new RandomizerDecorator(new FileRepository());
        $config = $this->getParameter('generator');
        $listing = [];

        foreach (['female', 'male'] as $gender) {
            foreach ($config as $idx => $combo) {
                for ($k = 0; $k < $card; $k++) {
                    $listing[$gender][$idx][] = $repo->getRandomGivenNameFor($gender, $combo[0]) . ' ' . $repo->getRandomSurnameFor($combo[1]);
                }
            }
        }

        return $this->render('random_name.html.twig', ['listing' => $listing]);
    }

    /**
     * Generates a QR Code for external initiative tracker
     */
    #[Route("/tracker/qrcode", methods: ["GET"])]
    public function tracker(VertexRepository $repo): Response
    {
        $lan = $this->generateUrl('app_tracker_show', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $iter = $repo->findByClass([Ali::class, Freeform::class, Transhuman::class]);

        return $this->render('tracker/qrcode.html.twig', ['listing' => new \App\Entity\Cursor\FighterIterator($iter), 'url_tracker' => $lan]);
    }

    /**
     * 3D View of digraph
     */
    #[Route("/digraph/view3d", methods: ["GET"])]
    public function digraph(): Response
    {
        return $this->render('digraph/view3d.html.twig');
    }

    /**
     * Returns the adjacency matrix of the undirected graph representing all Vertex(ices) in the "vertex" database collection
     * JSON format
     */
    #[Route("/digraph/load", methods: ["GET"])]
    public function getGraph(DigraphExplore $repo): JsonResponse
    {
        return new JsonResponse($repo->getNonDirectedGraphAdjacency());
    }

    /**
     * Ajax name generator. Fully random
     */
    #[Route("/ajax/name", methods: ["GET"])]
    public function ajaxName(Request $request): JsonResponse
    {
        $repo = new RandomizerDecorator(new FileRepository());
        $gender = $request->query->get('gender');
        $language = $request->query->get('language');
        $fullname = $repo->getRandomGivenNameFor($gender, 'random') . ' ' . $repo->getRandomSurnameFor($language);

        return new JsonResponse($fullname);
    }

    /**
     * Shows some stats on the current game
     * @param InfoDashboard $info
     * @return Response
     */
    public function summary(InfoDashboard $info): Response
    {
        return $this->render('summary.html.twig', ['stats' => $info]);
    }

}
