<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Ratchet\App;

/**
 * A websocket client that pushes messages to Websocket server
 * (lazy connection for services working environment)
 */
class WebsocketPusher
{

    const ROUTE_PICTURE = '/picture';
    const ROUTE_CUBEMAP = '/cubemap';

    protected $localIp;
    protected $wsPort;
    protected $logger;

    public function __construct(NetTools $nettools, \Psr\Log\LoggerInterface $websoxLogger, int $websocketPort)
    {
        $this->localIp = $nettools->getLocalIp();
        $this->wsPort = $websocketPort;
        $this->logger = $websoxLogger;
    }

    public function getUrlPicture(): string
    {
        return 'ws://' . $this->localIp . ':' . $this->wsPort . self::ROUTE_PICTURE;
    }

    public function getUrlCubemap(): string
    {
        return 'ws://' . $this->localIp . ':' . $this->wsPort . self::ROUTE_CUBEMAP;
    }

    public function createServer(): App
    {
        $app = new App($this->localIp, $this->wsPort, '0.0.0.0');
        $app->route(self::ROUTE_PICTURE, new PictureBroadcaster($this->logger), ['*']);
        $app->route(self::ROUTE_CUBEMAP, new PictureBroadcaster($this->logger), ['*']);

        return $app;
    }

    public function push(string $data, string $imgType): string
    {
        $route = ['2d' => self::ROUTE_PICTURE, '3d' => self::ROUTE_CUBEMAP][$imgType];
        $sp = new \Paragi\PhpWebsocket\Client($this->localIp, $this->wsPort, ['X-Pusher: Symfony'], $err, 10, false, false, $route);
        $sp->write($data);
        $reading = $sp->read();
        $this->logger->debug("Server responded with: $reading");

        return $reading;
    }

}
