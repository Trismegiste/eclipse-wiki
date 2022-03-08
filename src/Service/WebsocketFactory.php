<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Hoa\Socket\Client as SoCli;
use Hoa\Socket\Server as SeSo;
use Hoa\Websocket\Client;
use Hoa\Websocket\Server;

/**
 * Factory for Websocket communication
 */
class WebsocketFactory
{

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

    public function createServer(): Server
    {
        return new Server(new SeSo($this->getUrl()));
    }

}
