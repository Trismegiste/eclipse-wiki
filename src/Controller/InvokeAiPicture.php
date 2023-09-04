<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\AppendRemotePicture;
use App\Repository\VertexRepository;
use App\Service\InvokeAi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpClient\Exception\TransportException;
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

    public function __construct(protected InvokeAi $remote, protected VertexRepository $repository)
    {
        
    }

    protected function createSearchForm(): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder()
                        ->add('query')
                        ->add('search', SubmitType::class)
                        ->setMethod('GET')
                        ->getForm();
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
            try {
                $listing = $this->remote->searchPicture($form['query']->getData());
            } catch (UnexpectedValueException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (TransportException $e) {
                $this->addFlash('error', $e->getMessage());
            }
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
            try {
                $listing = $this->remote->searchPicture($form['query']->getData());
            } catch (UnexpectedValueException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (TransportException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('invokeai/vertex_search.html.twig', ['vertex' => $vertex, 'form' => $form->createView(), 'gallery' => $listing]);
    }

    #[Route('/vertex/{pk}/append/{pic}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function vertexAppend(string $pk, string $pic, Request $request): Response
    {
        $vertex = $this->repository->load($pk);
        $form = $this->createForm(AppendRemotePicture::class, $vertex, [
            'picture_url' => $this->remote->getAbsoluteUrl($pic),
            'thumbnail_url' => $this->remote->getThumbnailUrl($pic),
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

}
