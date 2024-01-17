<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Command\QrCode\ConsoleWriter;
use Endroid\QrCode\Builder\Builder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

/**
 * Generates a magic link to connect
 */
#[AsCommand(name: 'auth:get-link')]
class LoginLink extends Command
{

    protected $handler;
    protected $provider;

    public function __construct(LoginLinkHandlerInterface $loginLinkHandler, UserProviderInterface $repo, protected string $webLocalIp)
    {
        parent::__construct();
        $this->handler = $loginLinkHandler;
        $this->provider = $repo;
    }

    public function configure(): void
    {
        $this->setDescription('Generates login link to connect to the web server')
                ->addArgument('port', InputArgument::OPTIONAL, 'The port on which the web server is running', 80)
                ->addOption('firefox', 'f', InputOption::VALUE_NONE, 'Launch Firefox')
                ->addOption('qrcode', 'c', InputOption::VALUE_NONE, 'Print QR-Code')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Connecting to EclipseWiki app');

        $user = $this->provider->loadUserByIdentifier('gamemaster');
        $request = Request::create('http://' . $this->webLocalIp . ':' . $input->getArgument('port'));

        $loginLinkDetails = $this->handler->createLoginLink($user, $request);
        $loginLink = $loginLinkDetails->getUrl();

        // Print link
        $io->writeln(['Login link :', '', $loginLink, '']);

        // firefox
        if ($input->getOption('firefox')) {
            $browser = new Process(['firefox', $loginLink]);
            $browser->mustRun();
        }

        // print qr code
        if ($input->getOption('qrcode')) {
            $result = Builder::create()
                    ->writer(new ConsoleWriter())
                    ->data($loginLink)
                    ->build();

            $output->writeln($result->getString());
        }

        return self::SUCCESS;
    }

}
