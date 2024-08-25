<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Form\Llm\PromptFormFactory;
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

    const temperatureForListing = 1.5;

    public function __construct(
            protected RequestFactory $payloadFactory,
            protected PromptFormFactory $promptFactory,
            protected VertexRepository $repository,
            protected string $ollamaApi)
    {
        
    }

    /**
     * Generates a specific prompt for ollama and fills a form, on client-side, for the provided vertex
     * @param Request $request
     * @param Transhuman $npc
     * @return Response
     */
    #[Route('/vertex/{pk}/generate/{promptKey}', methods: ['GET', 'POST'])]
    public function contentGenerate(Request $request, string $pk, string $promptKey): Response
    {
        $vertex = $this->repository->load($pk);
        // Warning : the form in $append is PATCHed to the controller method below (see below)
        // This current method only deals with prompts and payloads generation for Ollama API, by using the form in $prompt
        $prompt = $this->promptFactory->createForContentGeneration($promptKey, $vertex);
        $append = $this->createForm(LlmOutputAppend::class, $vertex, [
            'action' => $this->generateUrl('app_ollama_contentappend', ['pk' => $vertex->getPk()]),
            'subtitle' => $this->promptFactory->getSubtitle($promptKey)
        ]);

        $payload = null;
        $prompt->handleRequest($request);
        if ($prompt->isSubmitted() && $prompt->isValid()) {
            $payload = $this->payloadFactory->create($prompt->getData()->prompt);
        }

        return $this->render('ollama/content_generate.html.twig', [
                    'ollama_api' => $this->ollamaApi,
                    'prompt' => $prompt->createView(),
                    'append' => $append->createView(),
                    'payload' => $payload
        ]);
    }

    /**
     * Generic update on the content of a vertex with LLM-generated output
     * @param Request $request
     * @param Vertex $vertex
     * @return Response
     */
    #[Route('/content/{pk}/append', methods: ['PATCH'])]
    public function contentAppend(Request $request, string $pk): Response
    {
        $vertex = $this->repository->load($pk);
        $form = $this->createForm(LlmOutputAppend::class, $vertex);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($vertex);
            $this->addFlash('success', 'Content from LLM appended');
        }

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
    }

    #[Route('/creation/listing/{promptKey}', methods: ['GET', 'POST'])]
    public function creationListing(Request $request, string $promptKey): Response
    {
        $prompt = $this->promptFactory->createForListingGeneration($promptKey);

        $payload = null;
        $prompt->handleRequest($request);
        if ($prompt->isSubmitted() && $prompt->isValid()) {
            $payload = $this->payloadFactory->create($prompt->getData()->prompt, self::temperatureForListing);
        }

        return $this->render('ollama/creation_listing.html.twig', [
                    'ollama_api' => $this->ollamaApi,
                    'title' => $promptKey,
                    'prompt' => $prompt->createView(),
                    'payload' => $payload,
                    'entry_dump' => $this->promptFactory->getEntryDump($promptKey)
        ]);
    }

    #[Route('/dramatron', methods: ['GET', 'POST'])]
    public function dramatron(Request $request): Response
    {
        return $this->render('ollama/dramatron.html.twig', [
                    'ollama_api' => $this->ollamaApi,
                    'payload' => $this->payloadFactory->create('init')
        ]);
    }

}
