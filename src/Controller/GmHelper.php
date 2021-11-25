<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Form\LoveLetter;
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
     * @Route("/")
     */
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("/gm/loveletter")
     */
    public function loveLetter(Request $request): Response
    {
        $form = $this->createForm(LoveLetter::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->render('loveletter.html.twig', $form->getData());
        }

        return $this->render('form.html.twig', ['title' => 'Love letter', 'form' => $form->createView()]);
    }

    /**
     * @Route("/gm/name")
     */
    public function nameGenerate($card = 15): Response
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

}
