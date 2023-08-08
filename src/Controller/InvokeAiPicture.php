<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Vertex;
use App\Repository\VertexRepository;
use App\Service\InvokeAi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
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
        $vertex = $this->repository->load($pk);

        $form = $this->createFormBuilder($vertex)
                ->add('content', TextType::class, [
                    'label' => "Prompt fragments",
                    'setter' => function (Vertex &$v, string $data) {
                        $v->setContent($v->getContent() . " [$data]");
                    },
                    'data' => null,
                    'constraints' => [new NotBlank()]
                ])
                ->add('append', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($form->getData());
            $this->addFlash('success', 'Ajouté');

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $pk]);
        }

        return $this->render('invokeai/append_vertex.html.twig', ['form' => $form->createView()]);
    }

    /**
     * AJAX Image search against InvokeAI api
     */
    #[Route('/ajax/search', methods: ['GET'])]
    public function ajaxSearch(Request $request): JsonResponse
    {
        $query = $request->query->get('q');
        try {
            $listing = $this->remote->searchPicture($query);
            return $this->json($listing);
        } catch (\Exception $e) {
            return $this->json([], 500);
        }
    }

}
