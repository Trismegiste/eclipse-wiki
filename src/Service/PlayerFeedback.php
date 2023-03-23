<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use SplObjectStorage;

/**
 * Feedback actions from Player
 */
class PlayerFeedback implements MessageComponentInterface
{

    protected $clients;
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->clients = new SplObjectStorage();
        $this->logger = $logger;
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        
    }

    public function onMessage(ConnectionInterface $conn, MessageInterface $msg)
    {
        $this->logger->info($msg);
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
    }

}
