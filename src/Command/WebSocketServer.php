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

        $this->webSocketServer->on('binary-message', function (Bucket $bucket) {
            $data = $bucket->getData();
            echo 'recieved ', "\n";
            $this->webSocketServer->broadcast(base64_encode($data['message']));
        });

        $this->webSocketServer->on('close', function (Bucket $bucket) {
            echo 'Bye', "\n";
        });

        $this->webSocketServer->run();

        return self::SUCCESS;
    }

    public function onOpen(Bucket $bucket)
    {
        $this->io->writeln('Welcome ' . $bucket->getSource()->getConnection()->getCurrentNode()->getId());
    }

}
