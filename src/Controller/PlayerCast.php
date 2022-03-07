<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\NetTools;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Hoa\Socket\Client as SoCli;
use Hoa\Websocket\Client;

/**
 * Ctrl for WebSocket-controled Player Screen
 */
class PlayerCast extends AbstractController
{

    protected $ntools;

    public function __construct(NetTools $utils)
    {
        $this->ntools = $utils;
    }

    /**
     * @Route("/player", methods={"GET"})
     */
    public function view(): Response
    {
        return $this->render('player/view.html.twig', ['host' => $this->getWebsocketHost()]);
    }

    /**
     * @Route("/player/qrcode", methods={"GET"})
     */
    public function qrCode(): Response
    {
        $url = $this->generateUrl('app_playercast_view', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $lan = preg_replace('#//localhost#', '//' . $this->ntools->getLocalIp(), $url);

        return $this->render('player/qrcode.html.twig', ['url_cast' => $lan]);
    }

    protected function getWebsocketHost(): string
    {
        return 'ws://' . $this->ntools->getLocalIp() . ':' . $this->getParameter('websocket_port');
    }

    /**
     * Pushes a picture to player screen
     * @Route("/player/push/{title}", methods={"GET"})
     */
    public function push(string $title, \App\Service\Storage $storage): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $client = new Client(new SoCli($this->getWebsocketHost()));
        $client->setHost('localhost');
        $client->connect();
        $client->send(file_get_contents($storage->getFileInfo($title)->getPathname()), null, Client::OPCODE_BINARY_FRAME);
        $client->close();

        return new \Symfony\Component\HttpFoundation\JsonResponse(null, Response::HTTP_OK);
    }

}
