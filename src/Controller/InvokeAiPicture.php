<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\InvokeAi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpClient\Exception\TransportException;
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

    public function __construct(protected InvokeAi $remote, protected \App\Repository\VertexRepository $repository)
    {
        
    }

    /**
     * Image search against InvokeAI api
     */
    #[Route('/search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $form = $this->createFormBuilder()
                ->add('query')
                ->add('search', SubmitType::class)
                ->setMethod('GET')
                ->getForm();

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

    #[Route('/vertex/{pk}/append', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function appendVertex(string $pk, Request $request): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $form = $this->createFormBuilder($vertex)
                ->add('append', SubmitType::class)
                ->getForm();
        if ($form->isSubmitted() && $form->isValid()) {
            
        }

        return $this->render('invokeai/append_vertex.html.twig', ['form' => $form->createView()]);
    }

}
