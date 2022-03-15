<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use Exception;
use Ratchet\WebSocket\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

/**
 * Server
 */
class PictureBroadcaster implements MessageComponentInterface
{

    protected $clients;
    protected $currentFile = null;
    protected $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->clients = new SplObjectStorage();
        $this->currentFile = new \SplFileInfo(join_paths(__DIR__, '../Command/mire.svg'));
        $this->logger = $logger;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->logger->info('New connection');

        $mime = mime_content_type($this->currentFile->getPathname());
        $conn->send('data:'
                . $mime . ';base64,'
                . base64_encode(file_get_contents($this->currentFile->getPathname())));
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->logger->debug('Receiving message ' . $msg);
        $message = json_decode($msg);
        $fileinfo = new \SplFileInfo($message->file);
        $this->currentFile = $fileinfo;
        $mime = mime_content_type($fileinfo->getPathname());

        $data = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fileinfo->getPathname()));

        $this->logger->debug("Broadcasting picture '$mime' to " . $this->clients->count() . ' clients');
        foreach ($this->clients as $client) {
            if ($from != $client) {
                $client->send($data);
            }
        }
        unset($data); // to force GC asap
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->logger->error($e->getMessage());
        $conn->close();
    }

}
