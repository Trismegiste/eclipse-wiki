<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Timeline;
use App\Entity\Vertex;
use App\Form\TimelineCreate;
use App\Form\TimelineType;
use App\Repository\VertexRepository;
use App\Service\DigraphExplore;
use App\Service\GameSessionTracker;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * CRUD for Timeline entity
 */
#[Route('/timeline')]
class TimelineCrud extends GenericCrud
{

    public function __construct(VertexRepository $repo, protected DigraphExplore $explorer)
    {
        parent::__construct($repo);
    }

    protected function createEntity(string $title): Vertex
    {
        return new Timeline($title);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate(TimelineCreate::class, 'timeline/create.html.twig', $request);
    }

    /**
     * @param string $pk
     * @param Request $request
     * @return Response
     */
    #[Route('/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(string $pk, Request $request): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $form = $this->createForm(TimelineType::class, $vertex, ['edit' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            $target = $form->get('create')->isClicked() ? 'app_vertexcrud_show' : 'app_timelinecrud_edit';

            return $this->redirectToRoute($target, ['pk' => $vertex->getPk()]);
        }

        return $this->render('timeline/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Fragment : explores the graph of Vertex and renders the tree from a Timeline vertex
     * @param Timeline $vertex
     * @return Response
     */
    #[Route('/partition/{pk}/summary', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function partitionSummary(Timeline $vertex): Response
    {
        $dump = $this->explorer->graphToSortedCategory($vertex);

        return $this->render('timeline/ajax/summary.html.twig', ['network' => $dump]);
    }

    /**
     * Pin the current timeline in the user session
     */
    #[Route('/pin/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pin(Timeline $timeline, GameSessionTracker $tracker): Response
    {
        $tracker->getDocument()->setTimeline($timeline);
        $this->addFlash('success', 'Scenario ' . $timeline->getTitle() . ' épinglé');

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $timeline->getPk()]);
    }

    /**
     * Show the list of broken links for a given timeline
     */
    #[Route('/broken/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function showBroken(Timeline $timeline): Response
    {
        return $this->render('timeline/partition/broken.html.twig', [
                    'timeline' => $timeline,
                    'broken' => $this->explorer->searchForBrokenLinkByTimeline($timeline)
        ]);
    }

    /**
     * Lists Vertices linked to this Timeline
     */
    #[Route('/partition/{pk}/list', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function partitionListing(Timeline $timeline): Response
    {
        $dump = $this->explorer->graphToSortedCategory($timeline);
        $title = array_merge(...array_values($dump));
        $iter = $this->repository->search(['title' => ['$in' => $title]]);

        return $this->render('timeline/partition/listing.html.twig', ['listing' => $iter, 'vertex' => $timeline]);
    }

    /**
     * Shows a visual listing of all Vertices linked to a Timeline
     * @param Timeline $vertex
     * @return Response
     */
    #[Route('/partition/{pk}/gallery', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function partitionGallery(Timeline $timeline, \App\Service\PartitionGalleryFactory $fac): Response
    {
        $partition = $this->explorer->getPartitionByTimeline()[$timeline->getTitle()];

        $pk = array_map(function ($val) {
            return new ObjectId($val);
        }, array_column($partition, 'pk'));

        $iter = $this->repository->search(['_id' => ['$in' => $pk]]);

        return $this->render('timeline/partition/gallery.html.twig', [
                    'vertex' => $timeline,
                    'gallery' => $fac->create($iter)
        ]);
    }

    #[Route('/listing', methods: ['GET'])]
    public function listing(): Response
    {
        $iter = $this->repository->searchTimeline();

        return $this->render('timeline/listing.html.twig', ['listing' => $iter]);
    }

}
