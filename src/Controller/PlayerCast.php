<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\Storage;
use App\Service\WebsocketFactory;
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
     * @Route("/player", methods={"GET"})
     */
    public function view(): Response
    {
        return $this->render('player/view.html.twig', ['host' => $this->factory->getUrl()]);
    }

    /**
     * @Route("/player/qrcode", methods={"GET"})
     */
    public function qrCode(\App\Service\NetTools $ntools): Response
    {
        $url = $this->generateUrl('app_playercast_view', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $lan = preg_replace('#//localhost#', '//' . $ntools->getLocalIp(), $url);

        return $this->render('player/qrcode.html.twig', ['url_cast' => $lan]);
    }

    /**
     * Pushes a picture to player screen
     * @Route("/player/push/{title}", methods={"POST"})
     */
    public function push(string $title, Storage $storage): JsonResponse
    {
        try {
            $client = $this->factory->createClient();
            $client->setHost('localhost');
            $client->connect();
            $client->send(json_encode([
                'file' => $storage->getFileInfo($title)->getPathname(),
                'title' => 'Toto'
            ]));
            $client->close();

            return new JsonResponse(['message' => "$title sent"], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
