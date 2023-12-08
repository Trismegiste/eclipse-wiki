<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\AppendRemotePicture;
use App\Repository\VertexRepository;
use App\Service\StableDiffusion\InvokeAiClient;
use App\Service\StableDiffusion\LocalRepository;
use App\Service\StableDiffusion\RepositoryChoice;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

/**
 * Controller for accessing remote picture on InvokeAI
 */
#[Route('/invokeai')]
class InvokeAiPicture extends AbstractController
{

    protected $source = [];

    public function __construct(LocalRepository $local, InvokeAiClient $remote, protected VertexRepository $repository)
    {
        $this->source = [
            RepositoryChoice::remote->value => $remote,
            RepositoryChoice::local->value => $local
        ];
    }

    protected function createSearchForm(): FormInterface
    {
        return $this->createFormBuilder()
                        ->add('query', TextType::class, ['attr' => ['x-model.fill' => 'query']])
                        ->add('search', SubmitType::class)
                        ->setMethod('GET')
                        ->getForm();
    }

    protected function processSearchWithFailOver(string $query): array
    {
        $listing = ['remote' => [], 'local' => []];
        //remote
        try {
            $listing['remote'] = $this->remote->searchPicture($query);
        } catch (UnexpectedValueException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (TransportException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        // failover local
        $listing['local'] = $this->local->searchPicture($query);

        return $listing;
    }

    /**
     * Image search against InvokeAI api
     */
    #[Route('/search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $form = $this->createSearchForm();

        return $this->render('invokeai/search.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/vertex/{pk}/search', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function vertexSearch(string $pk, Request $request): Response
    {
        $vertex = $this->repository->load($pk);

        $form = $this->createSearchForm();

        $listing = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $listing = $this->processSearchWithFailOver($form['query']->getData());
        }

        return $this->render('invokeai/vertex_search.html.twig', ['vertex' => $vertex, 'form' => $form->createView(), 'gallery' => $listing]);
    }

    #[Route('/{storage}/vertex/{pk}/append/{pic}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function vertexAppend(string $pk, RepositoryChoice $storage, string $pic, Request $request): Response
    {
        $vertex = $this->repository->load($pk);
        $form = $this->createForm(AppendRemotePicture::class, $vertex, [
            'picture_url' => $this->source[$storage->value]->getAbsoluteUrl($pic),
            'thumbnail_url' => $this->source[$storage->value]->getThumbnailUrl($pic),
            'default_name' => $vertex->getTitle() . ' - ' . $request->query->get('query')
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($form->getData());
            $this->addFlash('success', $form['local_name']->getData() . ' downloaded and append');

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $pk]);
        }

        return $this->render('invokeai/vertex_append.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/local/{pic}', methods: ['GET'])]
    public function getLocal(string $pic): BinaryFileResponse
    {
        return $this->source[RepositoryChoice::local->value]->getPictureResponse($pic);
    }

    /**
     * Image search against local storage of InvokeAI
     */
    #[Route('/ajax/{source}/search', methods: ['GET'])]
    public function ajaxSearch(RepositoryChoice $source, Request $request): Response
    {
        try {
            return new JsonResponse($this->source[$source->value]->searchPicture($request->query->get('q')));
        } catch (UnexpectedValueException $e) {
            return new JsonResponse(['level' => 'error', 'message' => $e->getMessage()], 400);
        } catch (TransportException $e) {
            return new JsonResponse(['level' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
