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
 * Controller for text generation with LLM
 */
#[Route('/ollama')]
class Ollama extends AbstractController
{

    #[Route('/index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(\App\Ollama\BackgroundPromptType::class);

        $prompt = '';
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prompt = $form->getData()->prompt;
        }

        return $this->render('ollama/index.html.twig', ['title' => 'Ollama', 'form' => $form->createView(), 'prompt' => $prompt]);
    }

}
