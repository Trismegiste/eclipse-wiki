<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use Knp\Snappy\Pdf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
    protected Pdf $pdfWriter;

    public function __construct(Environment $twig, Pdf $pdfWriter)
    {
        parent::__construct();
        $this->twig = $twig;
        $this->pdfWriter = $pdfWriter;
    }

    protected function configure()
    {
        $this->setDescription('Generate a book from the MW')
                ->addArgument('target', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Book');

        $this->pdfWriter->generateFromHtml(
                $this->twig->render('book/Eclipse Phase.html.twig', ['titre' => 'Eclipse Phase']),
                $input->getArgument('target'),
                ['page-size' => 'A5']
        );

        return self::SUCCESS;
    }

}
