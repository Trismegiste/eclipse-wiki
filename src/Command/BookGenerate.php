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
        $this->setDescription('Generate a book from the MediaWiki')
                ->addArgument('target', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $target = $input->getArgument('target');
        $content = $this->twig->render('book/testing.html.twig', ['titre' => 'Eclipse Phase']);
        file_put_contents("$target.html", $content);

        $this->pdfWriter->generateFromHtml(
                $content,
                $target,
                ['page-size' => 'A5'],
                true
        );

        return self::SUCCESS;
    }

}
