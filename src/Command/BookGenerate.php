<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

/**
 * Generates RPG books
 */
class BookGenerate extends Command
{

    protected static $defaultName = 'book:generate';
    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        parent::__construct();
        $this->twig = $twig;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Book');

        file_put_contents('index.html', $this->twig->render('book/Eclipse Phase.html.twig', ['title' => 'Eclipse Phase']));

        return self::SUCCESS;
    }

}
