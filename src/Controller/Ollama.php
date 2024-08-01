<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Form\LlmContentAppend;
use App\Ollama\BackgroundPromptType;
use App\Ollama\RequestFactory;
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

    public function __construct(protected RequestFactory $factory)
    {
        
    }

    #[Route('/npc/{pk}/background', methods: ['GET', 'POST'])]
    public function npcBackground(Request $request, Transhuman $npc): Response
    {
        $prompt = $this->createForm(BackgroundPromptType::class);
        $append = $this->createForm(LlmContentAppend::class, $npc, [
            'action' => $this->generateUrl('app_ollama_contentappend', ['pk' => $npc->getPk()])
        ]);

        $payload = null;
        $prompt->handleRequest($request);
        if ($prompt->isValid() && $prompt->isSubmitted()) {
            $payload = $this->factory->create($prompt->getData()->prompt);
        }

        return $this->render('ollama/index.html.twig', [
                    'title' => $npc->getTitle(),
                    'prompt' => $prompt->createView(),
                    'append' => $append->createView(),
                    'payload' => $payload
        ]);
    }

    #[Route('/content/{pk}/append', methods: ['PATCH'])]
    public function contentAppend(Request $request, Vertex $vertex): Response
    {
        $form = $this->createForm(LlmContentAppend::class, $vertex);

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            
        }

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
    }

}
