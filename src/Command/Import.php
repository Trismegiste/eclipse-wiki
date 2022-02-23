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
use ZipArchive;
use function join_paths;

/**
 * Overwrite database with a zip
 */
class Import extends Command
{

    protected static $defaultName = 'db:import';
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
        $this->addArgument('source', InputArgument::REQUIRED, 'Source zip file');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('source');
        $io = new SymfonyStyle($input, $output);
        $io->title("Import from $filename");

        $zip = new ZipArchive();
        if ($zip->open($filename) !== true) {
            throw new RuntimeException("cannot open <$filename>");
        }
        $zip->extractTo($this->store->getRootDir());
        $zip->close();

        $importJson = join_paths($this->store->getRootDir(), 'vertex.json');
        $dump = \MongoDB\BSON\toPHP(\MongoDB\BSON\fromJSON(file_get_contents($importJson)));

        $this->repo->delete(iterator_to_array($this->repo->search()));
        foreach ($dump as $vertex) {
            $io->writeln('Importing ' . get_class($vertex));
            $this->repo->save(clone $vertex);
        }
        $this->store->delete('vertex.json');

        return Command::SUCCESS;
    }

}
