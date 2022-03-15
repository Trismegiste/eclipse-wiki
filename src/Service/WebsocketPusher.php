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

    public function createServer(): App
    {
        $app = new App($this->localIp, $this->wsPort, '0.0.0.0');
        $app->route('/', new PictureBroadcaster(), ['*']);

        return $app;
    }

    public function push(string $data): void
    {
        \Ratchet\Client\connect($this->getUrl())->then(
                function ($conn) use ($data) {
                    $conn->on('message', function ($msg) use ($conn) {
                        echo "Received: {$msg}\n";
                        $conn->close();
                    });

                    $conn->send($data);
                },
                function ($e) {
                    echo "Could not connect: {$e->getMessage()}\n";
                }
        );
    }

}
