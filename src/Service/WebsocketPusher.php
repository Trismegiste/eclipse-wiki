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

    public function push(string $msg): void
    {
        $client = $this->factory->createClient();
        $client->setHost($this->domain);
        $client->connect();
        $client->send($msg);
        $client->close();
    }

}
