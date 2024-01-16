<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\PeeringConfirm;
use App\Form\Type\TopicSelectorType;
use App\Repository\VertexRepository;
use App\Service\DocumentBroadcaster;
use App\Service\Mercure\Pusher;
use App\Service\Mercure\SubscriptionClient;
use Exception;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Controller used by the GM for pushing picture/data/file to player(s)
 * @see PlayerLog controller for players side
 */
class GmPusher extends AbstractController
{

    public function __construct(protected Pusher $pusher)
    {
        
    }

    //  /!\ -- Big security breach : internally called ONLY -- /!\
    // DO NOT EXPOSE THIS CONTROLLER PUBLICLY
    public function internalPushPicture(string $pathname, string $imgType = 'picture'): JsonResponse
    {
        try {
            $pic = new SplFileInfo($pathname);
            $this->pusher->sendPictureAsDataUrl($pic, $imgType);
            return new JsonResponse(['level' => 'success', 'message' => $pic->getBasename() . ' sent'], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(['level' => 'error', 'message' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    #[Route("/push/{pk}/document/{filename}/{label}", methods: ["GET", "POST"])]
    public function pushDocument(Request $request, string $pk, string $filename, string $label, VertexRepository $repo, DocumentBroadcaster $broadcaster)
    {
        $vertex = $repo->findByPk($pk);
        $form = $this->createFormBuilder()
                ->add('channel', TopicSelectorType::class)
                ->add('push', SubmitType::class)
                ->getForm();

        $url = $broadcaster->getLinkToDocument($filename);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->pusher->sendDocumentLink($url, $label);
            $this->addFlash('success', "PDF $filename envoyé");

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('player/push_document.html.twig', [
                    'vertex' => $vertex,
                    'title' => $vertex->getTitle(),
                    'form' => $form->createView(),
                    'document' => [
                        'url' => $url,
                        'label' => $label
                    ]
        ]);
    }

    /**
     * Wait for peering with players
     */
    #[Route("/peering", methods: ["GET", "POST"])]
    public function peering(Request $request, SubscriptionClient $mercure): Response
    {
        $form = $this->createForm(PeeringConfirm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $assoc = $form->getData();
            $this->pusher->validPeering($assoc['key'], $assoc['npc']->getTitle());
        }

        return $this->render('peering.html.twig', [
                    'form' => $form->createView(),
                    'player_peering' => $this->generateUrl('app_playerlog_peering', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    'subscription' => $mercure->getSubscriptions()->subscriptions
        ]);
    }

}
