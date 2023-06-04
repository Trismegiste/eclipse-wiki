<?php

namespace App\Command;

use App\Service\WebsocketPusher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This is a WebSocket server that pushes pictures to player clients
 */
#[AsCommand(name: "playercast:daemon")]
class PlayerCastDaemon extends Command
{

    protected $factory;

    public function __construct(WebsocketPusher $fac)
    {
        parent::__construct();
        $this->factory = $fac;
    }

    protected function configure(): void
    {
        $this->setDescription('Lauch Playercast Daemon (Websocket server)');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("WebSocket Server listenig on " . $this->factory->getUrlPicture());

        $app = $this->factory->createServer();
        $app->run();

        return self::SUCCESS;
    }

}
