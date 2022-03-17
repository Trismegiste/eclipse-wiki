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
    protected $logger;

    public function __construct(NetTools $nettools, \Psr\Log\LoggerInterface $websoxLogger, int $websocketPort)
    {
        $this->localIp = $nettools->getLocalIp();
        $this->wsPort = $websocketPort;
        $this->logger = $websoxLogger;
    }

    public function getUrl(): string
    {
        return 'ws://' . $this->localIp . ':' . $this->wsPort;
    }

    public function createServer(): App
    {
        $app = new App($this->localIp, $this->wsPort, '0.0.0.0');
        $app->route('/', new PictureBroadcaster($this->logger), ['*']);

        return $app;
    }

    public function push(string $data): string
    {
        // open
        $sp = WebsocketClient::open($this->localIp, $this->wsPort, ['X-Pusher: Symfony']);

        if ($sp) {
            // write
            $written = WebsocketClient::write($sp, $data);
            if (false === $written) {
                throw new \RuntimeException('Unable to write to ' . $this->getUrl());
            }
            // read
            $reading = WebsocketClient::read($sp, $errstr);
            if (false === $reading) {
                throw new \RuntimeException('Unable to read from ' . $this->getUrl() . ', cause : ' . $errstr);
            }
            // log
            $this->logger->debug("Server responed with: $reading");

            return $reading;
        } else {
            throw new \RuntimeException('Unable to connect to ' . $this->getUrl());
        }
    }

}
