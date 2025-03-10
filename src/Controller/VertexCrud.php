<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Vertex;
use App\Form\VertexRename;
use App\Form\VertexType;
use App\Repository\VertexRepository;
use App\Service\DigraphExplore;
use App\Service\GameSessionTracker;
use App\Twig\SaWoExtension;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Vertex
 */
class VertexCrud extends GenericCrud
{

    public function __construct(VertexRepository $repo, protected GameSessionTracker $tracker)
    {
        parent::__construct($repo);
    }

    /**
     * Lists all vertex (and subclasses). The page calls the VertexCrud::filter controller with AJAX
     */
    #[Route('/vertex/list', methods: ['GET'])]
    public function list(/* some filters */): Response
    {
        return $this->render('vertex/list.html.twig');
    }

    /**
     * Showing a vertex vertex by its primary key
     */
    #[Route('/vertex/show/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function show(string $pk): Response
    {
        $subgraph = $this->repository->loadSubgraph($pk);
        $vertex = $subgraph->getFocus();
        $this->tracker->getDocument()->push($vertex);
        $template = SaWoExtension::showTemplate[get_class($vertex)];

        return $this->render($template, ['vertex' => $vertex, 'backlinks' => $subgraph->getInbound()]);
    }

    /**
     * Showing a vertex by its title. If it does not exist, redirect to creation
     */
    #[Route('/wiki/{title}', methods: ['GET'], name: 'app_wiki')]
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
     */
    #[Route('/vertex/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $fromLink = $request->query->get('title');

        return $this->render('vertex/create.html.twig', ['from_link' => $fromLink]);
    }

    /**
     * Editing a vertex (simple : title & content)
     */
    #[Route('/vertex/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(VertexType::class, 'vertex/edit.html.twig', $pk, $request);
    }

    /**
     * Deleting a vertex
     */
    #[Route('/vertex/delete/{pk}', methods: ['GET', 'DELETE'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function delete(string $pk, Request $request): Response
    {
        $subgraph = $this->repository->loadSubgraph($pk);
        $vertex = $subgraph->getFocus();

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

        return $this->render('vertex/delete.html.twig', ['form' => $form->createView(), 'backlinks' => $subgraph->getInbound()]);
    }

    /**
     * Ajax for searching vertices by title
     */
    #[Route('/vertex/search', methods: ['GET'])]
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
     */
    #[Route('/vertex/previous/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
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
     */
    #[Route('/vertex/next/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
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
     */
    #[Route('/vertex/rename/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function rename(string $pk, Request $request): Response
    {
        $subgraph = $this->repository->loadSubgraph($pk);
        $oldTitle = $subgraph->getFocus()->getTitle();

        $form = $this->createForm(VertexRename::class, $subgraph);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $subgraph = $form->getData();
            $this->repository->save($subgraph->all());
            $this->addFlash('success', sprintf("'%s' a été renommé en '%s' ansi que dans les %d backlinks",
                            $oldTitle,
                            $subgraph->getFocus()->getTitle(),
                            count($subgraph->getInbound())));

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $pk]);
        }

        return $this->render('vertex/rename.html.twig', [
                    'form' => $form->createView(),
                    'mention' => $this->repository->searchKeywordNotLink($oldTitle)
        ]);
    }

    /**
     * Ajax for the listing
     */
    #[Route('/vertex/filter', methods: ['GET'])]
    public function filter(Request $request): Response
    {
        $keyword = $request->query->get('query', '');
        $found = $this->repository->filterBy($keyword);

        return $this->render('fragment/listing_only.html.twig', ['listing' => $found]);
    }

    /**
     * Archiving a vertex
     */
    #[Route('/vertex/archive/{pk}', methods: ['GET', 'PATCH'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function archive(string $pk, Request $request): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $form = $this->createFormBuilder($vertex)
                ->add('archived', CheckboxType::class)
                ->add('archive', SubmitType::class)
                ->setMethod('PATCH')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($vertex);
            $this->addFlash('success', $vertex->getTitle() . ' a été archivé');

            return $this->redirectToRoute('app_vertexcrud_list');
        }

        return $this->render('vertex/archive.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Show the list of orphan vertices
     */
    #[Route('/digraph/orphan', methods: ['GET'])]
    public function showOrphan(DigraphExplore $explorer): Response
    {
        return $this->render('digraph/orphan.html.twig', ['orphan' => $explorer->findOrphan()]);
    }

    /**
     * Show the list of broken links (or missing pages)
     */
    #[Route('/digraph/broken', methods: ['GET'])]
    public function showBroken(DigraphExplore $explorer): Response
    {
        return $this->render('digraph/broken.html.twig', ['broken' => $explorer->searchForBrokenLink()]);
    }

    /**
     * Show statistics
     */
    #[Route('/digraph/stats', methods: ['GET'])]
    public function showStats(): Response
    {
        return $this->render('digraph/stats.html.twig', ['counting' => $this->repository->countByClass()]);
    }

}
