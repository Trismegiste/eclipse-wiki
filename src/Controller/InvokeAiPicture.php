<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\InvokeAi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for accessing remote picture on InvokeAI
 */
class InvokeAiPicture extends AbstractController
{

    public function __construct(protected InvokeAi $remote)
    {
        
    }

    /**
     * Image search against InvokeAI api
     */
    #[Route('/invokeai/search', methods: ['GET'])]
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
            $listing = $this->remote->searchPicture($form['query']->getData());
        }

        return $this->render('invokeai/search.html.twig', ['form' => $form->createView(), 'gallery' => $listing]);
    }

}
