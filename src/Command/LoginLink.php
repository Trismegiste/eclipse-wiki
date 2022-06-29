<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
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

    public function __construct(LoginLinkHandlerInterface $loginLinkHandler, UserProviderInterface $repo)
    {
        parent::__construct();
        $this->handler = $loginLinkHandler;
        $this->provider = $repo;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->provider->loadUserByIdentifier('gamemaster');
        $request = Request::create('http://localhost:8001/');

        $loginLinkDetails = $this->handler->createLoginLink($user, $request);
        $loginLink = $loginLinkDetails->getUrl();

        $output->writeln($loginLink);

        return self::SUCCESS;
    }

}
