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
use App\Service\NetTools;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Trismegiste\NameGenerator\FileRepository;
use Trismegiste\NameGenerator\RandomizerDecorator;

/**
 * Description of GmHelper
 */
class GmHelper extends AbstractController
{

    /**
     * Landing page
     */
    #[Route("/", methods: ["GET"])]
    public function index(): Response
    {
        return $this->render('landing.html.twig');
    }

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
    public function tracker(VertexRepository $repo, NetTools $ntools): Response
    {
        $listing = $repo->findByClass([Ali::class, Freeform::class, Transhuman::class]);
        $lan = $ntools->generateUrlForExternalAccess('app_tracker_show');

        return $this->render('tracker/qrcode.html.twig', ['listing' => $listing, 'url_tracker' => $lan]);
    }

    /**
     * Creates a QR Code for the link to player screen
     */
    #[Route("/broadcast/qrcode", methods: ["GET"])]
    public function qrCode(NetTools $ntools): Response
    {
        $lan = $ntools->generateUrlForExternalAccess('app_playercast_view');

        return $this->render('player/qrcode_picture.html.twig', ['url_cast' => $lan]);
    }

    /**
     * Creates a QR Code for the link to player screen
     */
    #[Route("/broadcast/qrcode3d", methods: ["GET"])]
    public function qrCode3d(NetTools $ntools): Response
    {
        $lan = $ntools->generateUrlForExternalAccess('app_firstperson_player');

        return $this->render('player/qrcode_fps.html.twig', ['url_cast' => $lan]);
    }

    /**
     * Help page
     */
    #[Route("/help", methods: ["GET"])]
    public function help(): Response
    {
        return $this->render('help.html.twig');
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

}
