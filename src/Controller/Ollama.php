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

    public function __construct(protected \App\Ollama\RequestFactory $factory) {}

    #[Route('/index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(\App\Ollama\BackgroundPromptType::class);

        $payload = null;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $payload = $this->factory->create($form->getData()->prompt);
        }

        return $this->render('ollama/index.html.twig', ['title' => 'Ollama', 'form' => $form->createView(), 'payload' => $payload]);
    }

}
