<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Command\QrCode\ConsoleWriter;
use App\Service\NetTools;
use Endroid\QrCode\Builder\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

/**
 * Generates a magic link to connect
 */
class LoginLink extends Command
{

    protected static $defaultName = 'app:link';
    protected $handler;
    protected $provider;
    protected $tools;

    public function __construct(LoginLinkHandlerInterface $loginLinkHandler, UserProviderInterface $repo, NetTools $tools)
    {
        parent::__construct();
        $this->handler = $loginLinkHandler;
        $this->provider = $repo;
        $this->tools = $tools;
    }

    public function configure()
    {
        $this->setDescription('Generates login link to connect to the web server')
                ->addArgument('port', InputArgument::REQUIRED, 'The port on which the web server is running');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->provider->loadUserByIdentifier('gamemaster');
        $request = Request::create('http://' . $this->tools->getLocalIp() . ':' . $input->getArgument('port'));

        $loginLinkDetails = $this->handler->createLoginLink($user, $request);
        $loginLink = $loginLinkDetails->getUrl();

        $output->writeln($loginLink);

        $result = Builder::create()
                ->writer(new ConsoleWriter($output))
                ->data($loginLink)
                ->build();

        $result->dump();

        return self::SUCCESS;
    }

}
