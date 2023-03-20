<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Service\Pdf\ChromiumPdfWriter;
use App\Service\Pdf\TocGenerator;
use App\Service\Pdf\Writer;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
                ->addOption('preview', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $template = 'book/' . ($input->getOption('preview') ? 'preview' : 'Eclipse Phase') . '.html.twig';
        $tmpFile = new SplFileInfo('tmp.pdf');
        $target = new SplFileInfo($input->getArgument('target'));

        $this->pdfWriter->renderToPdf($template, ['titre' => 'Eclipse Phase'], $tmpFile);

        $this->createToc($tmpFile, $target);
        unlink($tmpFile->getPathname());

        return self::SUCCESS;
    }

    protected function createToc(SplFileInfo $source, SplFileInfo $target): void
    {
        $tocgen = new TocGenerator();

        $receipe = $tocgen->extractMeta($source, 1, 1, 'Eclipse Phase');
        $receipe = $tocgen->extractMeta($source, 1, 1, 'Commencement', $receipe);
        $receipe = $tocgen->extractMeta($source, 1, 2, 'Cadre de', $receipe);

        $toc = $tocgen->generateToc($source, $receipe);

        $tocgen->injectToc($source, $target, $toc);
    }

}
