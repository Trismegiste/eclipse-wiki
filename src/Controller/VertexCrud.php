<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Vertex;
use App\Form\VertexType;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Vertex
 */
class VertexCrud extends AbstractController
{

    protected $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * @Route("/vertex/list", methods={"GET"})
     */
    public function list(/* some filters */): Response
    {
        $it = $this->repository->findAll();

        return $this->render('vertex/list.html.twig', ['listing' => $it]);
    }

    /**
     * @Route("/vertex/show/{pk}", methods={"GET"})
     */
    public function show(string $pk): Response
    {
        $vertex = $this->repository->findByPk($pk);

        return $this->render('vertex/show.html.twig', ['vertex' => $vertex]);
    }

    /**
     * @Route("/wiki/{title}", methods={"GET"}, name="app_wiki")
     */
    public function wikiShow(string $title): Response
    {
        $vertex = $this->repository->findByTitle($title);
        if (is_null($vertex)) {
            return $this->redirectToRoute('app_vertexcrud_create', ['title' => $title]);
        }

        return $this->render('vertex/show.html.twig', ['vertex' => $vertex]);
    }

    /**
     * @Route("/vertex/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        $obj = null;
        if ($request->query->has('title')) {
            $title = $request->query->get('title');
            $obj = new Vertex(ucfirst($title));
        }

        $form = $this->createForm(VertexType::class, $obj);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('vertex/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/vertex/edit/{pk}", methods={"GET","PUT"})
     */
    public function edit(string $pk, Request $request): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $form = $this->createFormBuilder($vertex)
                ->add('content', TextareaType::class, ['attr' => ['rows' => 32]])
                ->add('edit', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('vertex/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/vertex/delete/{pk}", methods={"GET","DELETE"})
     */
    public function delete(string $pk, Request $request): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $form = $this->createFormBuilder($vertex)
                ->add('delete', SubmitType::class)
                ->setMethod('DELETE')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->delete($vertex);

            return $this->redirectToRoute('app_vertexcrud_list');
        }

        return $this->render('vertex/delete.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/vertex/search", methods={"GET"})
     */
    public function search(Request $request): JsonResponse
    {
        $title = $request->query->get('q', '');
        $choice = $this->repository->searchStartingWith($title);
        array_walk($choice, function (&$v, $k) {
            $v = $v->title;
        });

        return new JsonResponse($choice);
    }

}
