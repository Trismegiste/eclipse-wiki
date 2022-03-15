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
class PlayerCastDaemon extends Command
{

    protected static $defaultName = "playercast:daemon";
    protected $factory;

    public function __construct(\App\Service\WebsocketPusher $fac)
    {
        parent::__construct();
        $this->factory = $fac;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("WebSocket Server listenig on " . $this->factory->getUrl());

        $app = $this->factory->createServer();
        $app->run();

        return self::SUCCESS;
    }

}
