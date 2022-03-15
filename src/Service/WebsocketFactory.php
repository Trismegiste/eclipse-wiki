<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Hoa\Socket\Client as SoCli;
use Hoa\Websocket\Client;
use Ratchet\App;
use Ratchet\Server\EchoServer;

/**
 * Factory for Websocket communication
 */
class WebsocketFactory
{

    protected $localIp;
    protected $wsPort;

    public function __construct(NetTools $nettools, int $websocketPort)
    {
        $this->localIp = $nettools->getLocalIp();
        $this->wsPort = $websocketPort;
    }

    public function getUrl(): string
    {
        return 'ws://' . $this->localIp . ':' . $this->wsPort;
    }

    public function createClient(): Client
    {
        return new Client(new SoCli($this->getUrl()));
    }

    public function createServer(): App
    {
        $app = new App($this->localIp, $this->wsPort, '0.0.0.0');
        $app->route('/', new PictureBroadcaster(), ['*']);

        return $app;
    }

}
