<?php

namespace App\Command;

use Hoa\Event\Bucket;
use Hoa\Socket\Server as SeSo;
use Hoa\Websocket\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Description of WebSocketServer
 */
class WebSocketServer extends Command
{

    protected static $defaultName = "websocket:server";
    protected $webSocketServer;
    protected $io;
    protected $localIp;

    public function __construct(\App\Service\NetTools $nettools)
    {
        parent::__construct();
        $this->localIp = $nettools->getLocalIp();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title("WebSocket Server listenig on " . $this->localIp);

        $this->webSocketServer = new Server(
                new SeSo('ws://' . $this->localIp . ':8889')
        );

        $this->webSocketServer->on('open', [$this, 'onOpen']);
        $this->webSocketServer->on('message', [$this, 'onMessage']);
        $this->webSocketServer->on('close', [$this, 'onClose']);

        $this->webSocketServer->run();

        return self::SUCCESS;
    }

    public function onOpen(Bucket $bucket): void
    {
        $cnx = $bucket->getSource()->getConnection();
        $this->io->writeln([
            'Welcome ' . $cnx->getCurrentNode()->getId(),
            'There are currently ' . count($cnx->getNodes()) . ' connected clients'
        ]);
        $this->io->newLine();
    }

    public function onClose(Bucket $bucket): void
    {
        $cnx = $bucket->getSource()->getConnection();
        $this->io->writeln([
            'Goodbye ' . $cnx->getCurrentNode()->getId(),
            'There are currently ' . count($cnx->getNodes()) . ' connected clients'
        ]);
        $this->io->newLine();
    }

    public function onMessage(Bucket $bucket): void
    {
        $data = $bucket->getData();
        $message = json_decode($data['message']);
        $mime = mime_content_type($message->file);
        $this->webSocketServer->broadcast('data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($message->file)));
        $this->io->writeln('Sending...');
        $this->io->newLine();
    }

}
