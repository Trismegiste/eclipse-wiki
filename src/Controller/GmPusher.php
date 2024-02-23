<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\PeeringConfirm;
use App\Form\Type\TopicSelectorType;
use App\Repository\VertexRepository;
use App\Service\DocumentBroadcaster;
use App\Service\FileIoClient;
use App\Service\Mercure\Pusher;
use Exception;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
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

    /**
     * Sends a file exsting in the Player cache storage. Technically the link is public but the GM can send it to a spacific channel A.K.A a player
     * @param Request $request
     * @param string $pk the primary key of the vertex associated with the file (for going back)
     * @param string $filename The filename of the file to send
     * @param string $label A human name for the file, used in the link
     * @param VertexRepository $repo
     * @param DocumentBroadcaster $broadcaster
     * @return Response
     */
    #[Route("/push/{pk}/document/{filename}/{label}", methods: ["GET", "POST"], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pushDocument(Request $request, string $pk, string $filename, string $label, VertexRepository $repo, DocumentBroadcaster $broadcaster): Response
    {
        $vertex = $repo->findByPk($pk);
        $form = $this->createFormBuilder()
                ->add('channel', TopicSelectorType::class)
                ->add('push', SubmitType::class)
                ->getForm();

        $url = $broadcaster->getLinkToDocument($filename);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $channel = $form['channel']->getData();
            $this->pusher->sendDocumentLink($url, $label, $channel);
            $this->addFlash('success', "PDF $filename envoyé");

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('gmpusher/push_document.html.twig', [
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
     * Waiting screen for peering with players
     */
    #[Route("/peering", methods: ["GET"])]
    public function peering(): Response
    {
        $form = $this->createForm(PeeringConfirm::class, null, [
            // technically, the URL is identical to the ajax controller URL
            // but it's clearer to understand that this form is sent through AJAX
            'action' => $this->generateUrl('app_gmpusher_ajaxpeering')
        ]);

        return $this->render('gmpusher/peering.html.twig', [
                    'form' => $form->createView(),
                    'player_peering' => $this->generateUrl('app_playerlog_peering', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
    }

    /**
     * Ajax for peering a player with its peering key - managed with symfony form
     */
    #[Route("/peering", methods: ["POST"])]
    public function ajaxPeering(Request $request): JsonResponse
    {
        $form = $this->createForm(PeeringConfirm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $assoc = $form->getData();
                $this->pusher->validPeering($assoc['key'], $assoc['pc']->getTitle());

                return new JsonResponse([
                    'level' => 'success',
                    'message' => $assoc['pc']->getTitle() . ' appairé avec la clef ' . $assoc['key'],
                    'remove' => $assoc['key']  // for removing the key on the listing
                ]);
            } else {
                $errMsg = implode(' ', array_map(function (FormError $v) {
                            return $v->getOrigin()->getName() . ' : ' . $v->getMessage();
                        }, iterator_to_array($form->getErrors(true, true))));
                return new JsonResponse(['level' => 'error', 'message' => $errMsg]);
            }
        }

        return new JsonResponse(['level' => 'error', 'message' => 'Invalid call'], 400);
    }

    /**
     * This controller uploads a file to cloud service for temporary storage and show a link (qrcode) to it
     * This is only a failover in case of degraded infrastructure during the session (no WiFi, no 5G bluetooth shared connection, etc.)
     * @param string $filename
     * @param DocumentBroadcaster $broadcaster
     * @param FileIoClient $client
     * @return Response
     */
    #[Route("/cloud/{filename}/share", methods: ["POST"])]
    public function cloudShare(string $filename, DocumentBroadcaster $broadcaster, FileIoClient $client): Response
    {
        $link = $client->upload(new \SplFileInfo($broadcaster->getLinkToDocument($filename)));

        return $this->render('gmpusher/cloud_share.html.twig', [
                    'document' => [
                        'label' => $filename,
                        'url' => $link
                    ]
        ]);
    }

}
