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
use App\Service\DocumentBroadcaster;
use App\Service\Ollama\RequestFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @param string $pk the primary key of the vertex that will store the llm-generated content
     * @param string $promptKey the key of the form type in PromptFormFactory
     * @return Response
     */
    #[Route('/vertex/{pk}/generate/{promptKey}', methods: ['GET', 'POST'])]
    public function contentGenerate(Request $request, string $pk, string $promptKey): Response
    {
        $vertex = $this->repository->load($pk);

        // Warning : the form in $append is PATCHed to the controller method below (see below)
        // This current method only deals with prompts and payloads generation for Ollama API, by using the form in $prompt
        $prompt = $this->promptFactory->createForContentGeneration($promptKey, $request->query->all('prefill'));
        $append = null;

        $prompt->handleRequest($request);
        if ($prompt->isSubmitted() && $prompt->isValid()) {
            $data = $prompt->getData();
            $append = $this->createForm(LlmOutputAppend::class, $vertex, [
                'action' => $this->generateUrl('app_ollama_contentappend', ['pk' => $vertex->getPk()])
            ]);
            // we pass the prompt parameters and the prompt query into the the append form
            $append['prompt_param']->setData(json_encode($data->param));
            $append['prompt_query']->setData($data->prompt);
        }

        return $this->render('ollama/content_generate.html.twig', [
                    'ollama_api' => $this->ollamaApi,
                    'prompt' => $prompt->createView(),
                    'append' => $append?->createView(),
                    'edited_vertex' => $vertex
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
                    'payload' => $payload
        ]);
    }

    #[Route('/dramatron', methods: ['GET'])]
    public function dramatron(): Response
    {
        return $this->render('ollama/dramatron.html.twig', [
                    'ollama_api' => $this->ollamaApi,
                    'payload' => $this->payloadFactory->create("Dans le contexte précédemment décrit, voici le synopsis du roman\n")
        ]);
    }

    #[Route('/dramatron', methods: ['POST'])]
    public function downloadDramatronEpub(Request $request, DocumentBroadcaster $builder): JsonResponse
    {
        $scenar = $request->toArray();
        $epub = $builder->generateScenarioEpub($scenar, 'LLM');

        return new JsonResponse(['level' => 'success', 'message' => $epub->getBasename() . " stocké en cache"]);
    }

}
