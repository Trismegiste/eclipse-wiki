<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\DocumentBroadcaster;
use App\Service\NetTools;
use App\Service\Storage;
use App\Service\WebsocketFactory;
use App\Service\WebsocketPusher;
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

    protected $factory;

    public function __construct(WebsocketFactory $fac)
    {
        $this->factory = $fac;
    }

    /**
     * The actual player screen updated with websocket
     * @Route("/player", methods={"GET"})
     */
    public function view(): Response
    {
        return $this->render('player/view.html.twig', ['host' => $this->factory->getUrl()]);
    }

    /**
     * Creates a QR Code for the link to player screen
     * @Route("/player/qrcode", methods={"GET"})
     */
    public function qrCode(NetTools $ntools): Response
    {
        $url = $this->generateUrl('app_playercast_view', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $lan = preg_replace('#//localhost#', '//' . $ntools->getLocalIp(), $url); // @todo hardcoded config

        return $this->render('player/qrcode.html.twig', ['url_cast' => $lan]);
    }

    /**
     * Pushes a picture (from the Storage) to player screen
     * @Route("/player/push/{title}", methods={"POST"})
     */
    public function push(string $title, Storage $storage, WebsocketPusher $client): JsonResponse
    {
        try {
            $client->push(json_encode([
                'file' => $storage->getFileInfo($title)->getPathname(),
                'action' => 'pictureBroadcast'
            ]));

            return new JsonResponse(['message' => "$title sent"], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Returns a generated document
     * @Route("/player/getdoc/{filename}", methods={"GET"})
     */
    public function getDocument(string $filename, DocumentBroadcaster $broad): Response
    {
        return $broad->createResponseForFilename($filename);
    }

}
