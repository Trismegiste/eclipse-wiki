<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Form\Llm\BackgroundPromptType;
use App\Form\LlmOutputAppend;
use App\Repository\VertexRepository;
use App\Service\Ollama\RequestFactory;
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
        // Warning : the form in $append is PATCHed to the controller method below (see below)
        // This current method only deal with prompt and payload generation for Ollama with the form in $prompt
        $prompt = $this->createForm(BackgroundPromptType::class);
        $append = $this->createForm(LlmOutputAppend::class, $npc, [
            'action' => $this->generateUrl('app_ollama_contentappend', ['pk' => $npc->getPk()]),
            'subtitle' => 'Background'
        ]);

        $payload = null;
        $prompt->handleRequest($request);
        if ($prompt->isSubmitted() && $prompt->isValid()) {
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
    public function contentAppend(Request $request, string $pk, VertexRepository $repo): Response
    {
        $vertex = $repo->load($pk);
        $form = $this->createForm(LlmOutputAppend::class, $vertex);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($vertex);
            $this->addFlash('success', 'Content from LLM appended');
        }

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
    }

}
