<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Timeline;
use App\Entity\Vertex;
use App\Form\TimelineCreate;
use App\Form\TimelineDebrief;
use App\Form\TimelineType;
use App\Repository\VertexRepository;
use App\Service\DigraphExplore;
use App\Service\GameSessionTracker;
use App\Service\PartitionGalleryFactory;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $form = $this->createForm(TimelineType::class, $vertex, ['method' => 'PUT']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            $target = $form->get('create')->isClicked() ? 'app_vertexcrud_show' : 'app_timelinecrud_edit';

            return $this->redirectToRoute($target, ['pk' => $vertex->getPk()]);
        }

        return $this->render('timeline/edit.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/debrief/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function debrief(Timeline $vertex, Request $request): Response
    {
        $form = $this->createForm(TimelineDebrief::class, $vertex, ['method' => 'PUT']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('timeline/debrief.html.twig', ['form' => $form->createView(), 'title' => $vertex->getTitle()]);
    }

    /**
     * Ajax fragment : explores the graph of Vertex and renders the tree from a Timeline vertex
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
     * Pins the current timeline in the user session
     */
    #[Route('/pin/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pin(Timeline $timeline, GameSessionTracker $tracker): Response
    {
        $tracker->getDocument()->setTimeline($timeline);
        $this->addFlash('success', 'Scenario ' . $timeline->getTitle() . ' épinglé');

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $timeline->getPk()]);
    }

    /**
     * Shows the list of broken links for a given timeline
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
        $iter = $this->explorer->getPartitionListing($timeline);

        return $this->render('timeline/partition/listing.html.twig', ['listing' => $iter, 'vertex' => $timeline]);
    }

    /**
     * Shows a visual listing of all Vertices linked to a Timeline
     * @param Timeline $vertex
     * @return Response
     */
    #[Route('/partition/{pk}/gallery', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function partitionGallery(Timeline $timeline, PartitionGalleryFactory $fac): Response
    {
        $partition = $this->explorer->getPartitionByTimeline()[$timeline->getTitle()];

        $pk = array_map(function ($val) {
            return new ObjectId($val);
        }, array_column($partition, 'pk'));

        $iter = $this->repository->search(['_id' => ['$in' => $pk]]);

        return $this->render('timeline/partition/gallery.html.twig', [
                    'vertex' => $timeline,
                    'gallery' => $fac->createGalleryPerCategory($iter)
        ]);
    }

    /**
     * Lists all Timeline
     * @return Response
     */
    #[Route('/listing', methods: ['GET'])]
    public function listing(): Response
    {
        $iter = $this->repository->searchTimeline();

        return $this->render('timeline/listing.html.twig', ['listing' => $iter]);
    }

    /**
     * Show highlighted vertex for a Timeline
     * @param Timeline $timeline
     * @return Response
     */
    #[Route('/partition/{pk}/poster', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function partitionPoster(Timeline $timeline, PartitionGalleryFactory $fac): Response
    {
        $highlight = $this->explorer->getVertexSortedByCentrality($timeline);

        return $this->render('timeline/partition/poster.html.twig', [
                    'vertex' => $timeline,
                    'gallery' => $fac->createMoviePoster($highlight)
        ]);
    }

}
