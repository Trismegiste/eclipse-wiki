<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Vertex;
use App\Service\DocumentBroadcaster;
use App\Service\Mercure\Pusher;
use Exception;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Ctrl for WebSocket-controled Player Screen
 */
class PlayerCast extends AbstractController
{

    public function __construct(protected Pusher $pusher)
    {
        
    }

    /**
     * The actual player screen updated with websocket
     */
    #[Route('/player/view', methods: ['GET'])]
    public function view(): Response
    {
        return $this->render('player/view.html.twig', ['url_picture' => $this->pusher->getUrlPicture()]);
    }

    /**
     * Returns a generated document
     */
    #[Route('/player/getdoc/{filename}', methods: ['GET'])]
    public function getDocument(string $filename, DocumentBroadcaster $broad): Response
    {
        return $broad->createResponseForFilename($filename);
    }

    //  /!\ -- Big security breach : internally called ONLY -- /!\
    // DO NOT EXPOSE THIS CONTROLLER PUBLICLY
    public function internalPushFile(string $pathname, string $imgType = 'picture'): JsonResponse
    {
        try {
            $pic = new SplFileInfo($pathname);
            $this->pusher->sendPictureAsDataUrl($pic, $imgType);
            return new JsonResponse(['level' => 'success', 'message' => $pic->getBasename() . ' sent'], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(['level' => 'error', 'message' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    public function internalPushDynamicDocument(Vertex $vertex, string $filename, string $linkLabel)
    {
        $form = $this->createFormBuilder()
                ->add('push', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
                ->getForm();

        return $this->render('player/push_document.html.twig', [
                    'vertex' => $vertex,
                    'title' => $vertex->getTitle(),
                    'form' => $form->createView(),
                    'document' => [
                        'url' => $this->generateUrl('app_playercast_getdocument',
                                ['filename' => $filename],
                                UrlGeneratorInterface::ABSOLUTE_URL),
                        'label' => $linkLabel
                    ]
        ]);
    }

}
