<?php

namespace App\Command;

use App\Service\WebsocketFactory;
use Hoa\Event\Bucket;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This is a WebSocket server that pushes pictures to player clients
 */
class PicturePusher extends Command
{

    protected static $defaultName = "playercast:daemon";
    protected $webSocketServer;
    protected $io;
    protected $currentFile = null;
    protected $factory;

    public function __construct(WebsocketFactory $fac)
    {
        parent::__construct();
        $this->factory = $fac;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title("WebSocket Server listenig on " . $this->factory->getUrl());

        $this->webSocketServer = $this->factory->createServer();

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

        if (!is_null($this->currentFile)) {
            $this->io->writeln('And pushing last picture');
            $mime = mime_content_type($this->currentFile->getPathname());
            $this->webSocketServer->send('data:'
                . $mime . ';base64,'
                . base64_encode(file_get_contents($this->currentFile->getPathname())),
                $cnx->getCurrentNode()
            );
        }
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
        $fileinfo = new SplFileInfo($message->file);
        $this->currentFile = $fileinfo;
        $mime = mime_content_type($fileinfo->getPathname());
        $this->webSocketServer->broadcast('data:' . $mime . ';base64,' . base64_encode(file_get_contents($fileinfo->getPathname())));
        $this->io->writeln('Pushing ' . $fileinfo->getBasename());
        $this->io->newLine();
    }

}
