<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use SplFileInfo;
use SplObjectStorage;
use function join_paths;

/**
 * Server
 */
class PictureBroadcaster implements MessageComponentInterface
{

    protected $clients;
    protected $currentFile = null;
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->clients = new SplObjectStorage();
        $this->currentFile = new SplFileInfo(join_paths(__DIR__, '../Command/mire.svg'));
        $this->logger = $logger;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        /* Some explanations here : 
         * Since we add a route on \Ratchet\App, this class is wrapped in a \Ratchet\WebSocket\WsServer
         * Therefore, this wrapper decorates the \Ratchet\Client\WebSocket by inserting a property httpRequest
         * into the connection object.
         */
        /** @var RequestInterface $request */
        $request = $conn->httpRequest;
        $this->logger->info('New connection from ' . $this->getFirstUserAgent($request));

        if (!$this->isRequestFromSymfony($request)) {
            $this->clients->attach($conn); // we only track connections from web brower clients
            $mime = mime_content_type($this->currentFile->getPathname());
            $this->logger->debug('Pushing last picture');
            $conn->send('data:'
                . $mime . ';base64,'
                . base64_encode(file_get_contents($this->currentFile->getPathname())));
        }
    }

    private function getFirstUserAgent(RequestInterface $request): string
    {
        $identifier = 'Unknown';
        if ($request->hasHeader('User-Agent')) {
            $userAgentList = $request->getHeader('User-Agent');
            $identifier = $userAgentList[0];
        }

        return $identifier;
    }

    private function isRequestFromSymfony(RequestInterface $request): bool
    {
        $origin = $request->getHeader('X-Pusher');

        return (count($origin) > 0) && in_array('Symfony', $origin);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->logger->debug('Websocket Server receiving message ' . $msg . ' from ' . $this->getFirstUserAgent($from->httpRequest));
        $message = json_decode($msg);
        $fileinfo = new SplFileInfo($message->file);
        $this->currentFile = $fileinfo;
        $mime = mime_content_type($fileinfo->getPathname());

        $data = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fileinfo->getPathname()));

        $this->logger->debug("Websocket Server broadcasting " . $fileinfo->getBasename() . " to " . $this->clients->count() . ' web browser clients');
        foreach ($this->clients as $client) {
            $client->send($data);
        }
        unset($data); // to force GC asap

        if ($this->isRequestFromSymfony($from->httpRequest)) {
            $from->send("Broadcast of " . $fileinfo->getBasename() . " to " . $this->clients->count() . ' clients complete');
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->logger->info($this->getFirstUserAgent($conn->httpRequest) . ' is disconnecting');
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->logger->error($e->getMessage());
        $conn->close();
    }

}
