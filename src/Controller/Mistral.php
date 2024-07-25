<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Description of Mistral
 *
 * @author florent
 */
#[Route('/mistral')]
class Mistral extends AbstractController
{

    #[Route('/index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(\App\Ollama\BackgroundPromptType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            var_dump($form->getData());
        }

        return $this->render('form.html.twig', ['title' => 'Mistral', 'form' => $form->createView()]);
    }

}
