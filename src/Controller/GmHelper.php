<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Ali;
use App\Entity\Transhuman;
use App\Form\LoveLetter;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('landing.html.twig');
    }

    /**
     * Names generator
     * @Route("/gm/name/{card}", methods={"GET"})
     */
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
     * @Route("/tracker", methods={"GET"})
     */
    public function tracker(VertexRepository $repo): Response
    {
        $listing = $repo->findByClass([Ali::class, Transhuman::class]);

        return $this->render('tracker.html.twig', ['listing' => $listing]);
    }

}
