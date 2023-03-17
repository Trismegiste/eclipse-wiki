<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Service\Pdf\ChromiumPdfWriter;
use App\Service\Pdf\Writer;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generates RPG books
 */
class BookGenerate extends Command
{

    protected static $defaultName = 'book:generate';
    protected Writer $pdfWriter;

    public function __construct(ChromiumPdfWriter $pdfWriter)
    {
        parent::__construct();
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

        $this->pdfWriter->renderToPdf('book/testing.html.twig', ['titre' => 'Eclipse Phase'], new SplFileInfo($target));

        return self::SUCCESS;
    }

}
