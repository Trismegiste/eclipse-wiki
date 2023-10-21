<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\AppendRemotePicture;
use App\Form\Type\SubmitWaitType;
use App\Repository\VertexRepository;
use App\Service\StableDiffusion\InvokeAi;
use App\Service\StableDiffusion\LocalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

    public function __construct(protected LocalRepository $local, protected InvokeAi $remote, protected VertexRepository $repository)
    {
        
    }

    protected function createSearchForm(): FormInterface
    {
        return $this->createFormBuilder()
                        ->add('query')
                        ->add('search', SubmitWaitType::class)
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

        $listing = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $listing = $this->processSearchWithFailOver($form['query']->getData());
        }

        return $this->render('invokeai/search.html.twig', ['form' => $form->createView(), 'gallery' => $listing]);
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

    #[Route('/vertex/{pk}/append/{storage}/{pic}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function vertexAppend(string $pk, string $storage, string $pic, Request $request): Response
    {
        $chain = [
            'local' => $this->local,
            'remote' => $this->remote
        ];

        $vertex = $this->repository->load($pk);
        $form = $this->createForm(AppendRemotePicture::class, $vertex, [
            'picture_url' => $chain[$storage]->getAbsoluteUrl($pic),
            'thumbnail_url' => $chain[$storage]->getThumbnailUrl($pic),
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
        return $this->local->getPictureResponse($pic);
    }

    /**
     * Image search against local storage of InvokeAI
     */
    #[Route('/ajax/search', methods: ['GET'])]
    public function ajaxSearch(Request $request): Response
    {
        $listing['local'] = $this->local->searchPicture($request->query->get('q'));

        return new \Symfony\Component\HttpFoundation\JsonResponse($listing);
    }

}
