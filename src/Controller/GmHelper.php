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
     * Names generator
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
        $listing = $repo->findByClass([Ali::class, Freeform::class, Transhuman::class]);
        $lan = $this->generateUrl('app_tracker_show', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('tracker/qrcode.html.twig', ['listing' => $listing, 'url_tracker' => $lan]);
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
     * load the graph
     */
    #[Route("/digraph/load", methods: ["GET"])]
    public function getGraph(DigraphExplore $repo): JsonResponse
    {
        return new JsonResponse($repo->getNonDirectedGraphAdjacency());
    }

    /**
     * Ajax name generator
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

    public function summary(InfoDashboard $info): Response
    {
        return $this->render('summary.html.twig', ['stats' => $info]);
    }

}
