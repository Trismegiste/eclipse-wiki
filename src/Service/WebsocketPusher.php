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

    public function createServer(): App
    {
        $app = new App($this->localIp, $this->wsPort, '0.0.0.0');
        $app->route(self::ROUTE_PICTURE, new PictureBroadcaster($this->logger), ['*']);

        return $app;
    }

    public function push(string $data): string
    {
        $sp = new \Paragi\PhpWebsocket\Client($this->localIp, $this->wsPort, ['X-Pusher: Symfony'], $err, 10, false, false, self::ROUTE_PICTURE);
        $sp->write($data);
        $reading = $sp->read();
        $this->logger->debug("Server responded with: $reading");

        return $reading;
    }

}
