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
    public function ajaxName(\Symfony\Component\HttpFoundation\Request $request): JsonResponse
    {
        $repo = new RandomizerDecorator(new FileRepository());
        $gender = $request->query->get('gender');
        $language = $request->query->get('language');
        $fullname = $repo->getRandomGivenNameFor($gender, 'random') . ' ' . $repo->getRandomSurnameFor($language);

        return new JsonResponse($fullname);
    }

    public function summary(\App\Service\InfoDashboard $info): Response
    {
        return $this->render('summary.html.twig', ['stats' => $info]);
    }

    /**
     * Wait for peering with players
     */
    #[Route("/peering", methods: ["GET", "POST"])]
    public function peering(\App\Service\Mercure\Pusher $pusher, VertexRepository $vertexRepo, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $form = $this->createFormBuilder()
                ->add('key', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, ['attr' => ['x-model' => 'selectedKey']])
                ->add('npc', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                    'choices' => $vertexRepo->findByClass(Transhuman::class),
                    'choice_label' => function ($choice, string $key, mixed $value): TranslatableMessage|string {
                        return $choice->getTitle();
                    }
                ])
                ->add('peering', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $assoc = $form->getData();
            $pusher->validPeering($assoc['key'], $assoc['npc']->getTitle());
        }

        return $this->render('peering.html.twig', ['form' => $form->createView()]);
    }

}
