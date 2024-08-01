<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Form\LlmOutputAppend;
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

    /**
     * Generates a specific prompt for ollama and fills a form, on client-side, for the provided vertex
     * @param Request $request
     * @param Transhuman $npc
     * @return Response
     */
    #[Route('/npc/{pk}/background', methods: ['GET', 'POST'])]
    public function npcBackground(Request $request, Transhuman $npc): Response
    {
        $prompt = $this->createForm(BackgroundPromptType::class);
        $append = $this->createForm(LlmOutputAppend::class, $npc, [
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

    /**
     * Generic updates the content of a vertex with LLM-generated output
     * @param Request $request
     * @param Vertex $vertex
     * @return Response
     */
    #[Route('/content/{pk}/append', methods: ['PATCH'])]
    public function contentAppend(Request $request, Vertex $vertex): Response
    {
        $form = $this->createForm(LlmOutputAppend::class, $vertex);

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            
        }

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
    }

}
