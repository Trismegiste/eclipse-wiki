<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Repository\VertexRepository;
use App\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\SplFileInfo;
use ZipArchive;

/**
 * Export database into zip
 */
class Export extends Command
{

    const vertexFilename = 'vertex.json';

    protected static $defaultName = 'db:export';
    protected $repo;
    protected $store;

    public function __construct(VertexRepository $repo, Storage $store)
    {
        parent::__construct();
        $this->repo = $repo;
        $this->store = $store;
    }

    public function configure()
    {
        $this->addArgument('target', InputArgument::REQUIRED, 'Target zip file');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('target');
        $io = new SymfonyStyle($input, $output);
        $io->title("Export to $filename");

        $iter = $this->repo->search();
        $export = \MongoDB\BSON\toJSON(\MongoDB\BSON\fromPHP(iterator_to_array($iter)));

        $zip = new ZipArchive();
        if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("cannot open <$filename>");
        }

        // DB
        $io->info('Compress Vertices');
        $zip->addFromString(self::vertexFilename, $export);
        $io->success('Vertices Added');

        $io->info('Compress Storage');
        /** @var SplFileInfo $img */
        foreach ($this->store->searchByName('*') as $img) {
            $zip->addFile($img->getPathname(), $img->getFilename());
            $io->writeln('Adding ' . $img->getFilename());
        }
        $io->success('Storage Added');

        $zip->close();

        return Command::SUCCESS;
    }

}
