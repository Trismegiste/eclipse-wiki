<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Vertex;
use App\Form\VertexType;
use App\Twig\SaWoExtension;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Vertex
 */
class VertexCrud extends GenericCrud
{

    /**
     * Lists all vertex (and subclasses). The page calls the VertexCrud::filter controller with AJAX
     * @Route("/vertex/list", methods={"GET"})
     */
    public function list(/* some filters */): Response
    {
        return $this->render('vertex/list.html.twig');
    }

    /**
     * Showing a vertex vertex by its primary key
     * @Route("/vertex/show/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function show(string $pk): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $backlinks = $this->repository->searchByBacklinks($vertex->getTitle());
        $template = SaWoExtension::showTemplate[get_class($vertex)];

        return $this->render($template, ['vertex' => $vertex, 'backlinks' => $backlinks]);
    }

    /**
     * Showing a vertex by its title. If it does not exist, redirect to creation
     * @Route("/wiki/{title}", methods={"GET"}, name="app_wiki")
     */
    public function wikiShow(string $title): Response
    {
        $vertex = $this->repository->findByTitle($title);
        if (is_null($vertex)) {
            return $this->redirectToRoute('app_vertexcrud_create', ['title' => $title]);
        }

        return $this->show($vertex->getPk());
    }

    /**
     * Creates a new vertex
     * @Route("/vertex/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(VertexType::class, 'vertex/create.html.twig', $request);
    }

    /**
     * Editing a vertex (simple : title & content)
     * @Route("/vertex/edit/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(VertexType::class, 'vertex/edit.html.twig', $pk, $request);
    }

    /**
     * Deleting a vertex
     * @Route("/vertex/delete/{pk}", methods={"GET","DELETE"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function delete(string $pk, Request $request): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $form = $this->createFormBuilder($vertex)
                ->add('delete', SubmitType::class, ['attr' => ['class' => 'button-delete']])
                ->setMethod('DELETE')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->delete($vertex);
            $this->addFlash('success', $vertex->getTitle() . ' a été supprimé');

            return $this->redirectToRoute('app_vertexcrud_list');
        }

        return $this->render('vertex/delete.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Ajax for searching vertices by title
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

    /**
     * Show previous vertex from a PK
     * @Route("/vertex/previous/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function seekPrevious(string $pk): Response
    {
        $vertex = $this->repository->searchPreviousOf($pk);
        if (!is_null($vertex)) {
            $pk = $vertex->getPk();
        }

        return $this->show($pk);
    }

    /**
     * Show next vertex from a PK
     * @Route("/vertex/next/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function seekNext(string $pk): Response
    {
        $vertex = $this->repository->searchNextOf($pk);
        if (!is_null($vertex)) {
            $pk = $vertex->getPk();
        }

        return $this->show($pk);
    }

    /**
     * Renaming a vertex
     * @Route("/vertex/rename/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function rename(string $pk, Request $request): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $oldTitle = $vertex->getTitle();
        $backlinks = $this->repository->searchByBacklinks($vertex->getTitle());

        $form = $this->createFormBuilder($vertex)
                ->add('title', TextType::class, ['label' => 'Nouveau nom'])
                ->add('rename', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->renameTitle($oldTitle, $form->getData()->getTitle());
            $this->addFlash('success', "'$oldTitle' a été renommé");

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('vertex/rename.html.twig', ['form' => $form->createView(), "backlinks" => $backlinks]);
    }

    /**
     * Ajax for the listing
     * @Route("/vertex/filter", methods={"GET"})
     */
    public function filter(Request $request): Response
    {
        $keyword = $request->query->get('query', '');
        $found = $this->repository->filterBy($keyword);

        return $this->render('fragment/listing_only.html.twig', ['listing' => $found]);
    }

    protected function createEntity(string $title): Vertex
    {
        return new Vertex($title);
    }

}
