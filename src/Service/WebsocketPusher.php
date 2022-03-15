<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

/**
 * A websocket client that pushes messages to Websocket server
 * (lazy connection for services working environment)
 */
class WebsocketPusher
{

    protected $domain;
    protected $factory;

    public function __construct(WebsocketFactory $fac, string $domain = 'localhost')
    {
        $this->factory = $fac;
        $this->domain = $domain;
    }

    public function push(string $data): void
    {
        \Ratchet\Client\connect($this->factory->getUrl())->then(
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
