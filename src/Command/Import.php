<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Repository\VertexRepository;
use App\Service\Storage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ZipArchive;

/**
 * Overwrite database with a zip
 */
#[AsCommand(name: 'db:import')]
class Import extends Command
{

    const delayForTimestamp = 1000;

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
        $this->addArgument('source', InputArgument::REQUIRED, 'Source zip file')
                ->setDescription("OVERWRITE all pictures and vertices from one big ZIP file");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('source');
        $io = new SymfonyStyle($input, $output);
        $env = $this->getApplication()->getKernel()->getEnvironment();
        $io->title("Import '$filename' file into '$env' environment");

        $answer = $io->confirm("You're going to delete all existing vertices. Do you confirm ?", false);
        if (!$answer) {
            $io->warning('Exiting without any changes');
            return Command::SUCCESS;
        }

        $zip = new ZipArchive();
        if ($zip->open($filename) !== true) {
            throw new RuntimeException("cannot open <$filename>");
        }
        $io->info('Extracting files into Storage');
        $zip->extractTo($this->store->getRootDir());
        $zip->close();

        $importJson = $this->store->getFileInfo(Export::vertexFilename);
        $dump = \MongoDB\BSON\toPHP(\MongoDB\BSON\fromJSON(file_get_contents($importJson->getPathname())));

        $io->info('Delete old vertices');
        $this->repo->delete(iterator_to_array($this->repo->search()));

        $io->info('Inserting vertices');
        foreach ($dump as $vertex) {
            $io->writeln('Importing ' . $vertex->getCategory() . ' "' . $vertex->getTitle() . '"');
            $this->repo->save(clone $vertex);
            usleep(self::delayForTimestamp);
        }
        $this->store->delete(Export::vertexFilename);

        $io->success('Import succeed');

        return Command::SUCCESS;
    }

}
