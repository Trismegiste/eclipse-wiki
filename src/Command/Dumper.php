<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Entity\MediaWikiPage;
use App\Service\MediaWiki;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trismegiste\Strangelove\MongoDb\Repository;

/**
 * Dump html pages from the MediaWiki to MongoDb
 */
#[AsCommand(name: 'mediawiki:dump-to-local')]
class Dumper extends Command
{

    protected $repository;
    protected $mediaWiki;

    public function __construct(MediaWiki $mw, Repository $pageRepo)
    {
        $this->repository = $pageRepo;
        $this->mediaWiki = $mw;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
                ->setDescription('Dump remote MediaWiki pages to MongoDb')
                ->addArgument('category', InputArgument::REQUIRED)
                ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'How many', 50);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $category = $input->getArgument('category');
        $io->title("Dumping $category");

        $page = $this->mediaWiki->searchPageFromCategory($category, $input->getOption('limit'));
        if (count($page) == 0) {
            throw new RuntimeException("The category $category is empty, nothing to import");
        }
        $io->success("Found " . \count($page) . ' pages');

        // delete old :
        $io->section('Delete old...');
        $it = $this->repository->search(['category' => $category]);
        $this->repository->delete(iterator_to_array($it));

        // dump :
        $io->section('Dumping...');
        $io->progressStart(\count($page));
        foreach ($page as $item) {
            $entity = new MediaWikiPage($item->title, $category);
            // content
            $entity->content = $this->mediaWiki->getWikitextByName($item->title);
            $this->repository->save($entity);
            $io->progressAdvance();
            usleep(100000); // to prevent DDoS
        }
        $io->progressFinish();

        $io->success(\count($page) . " pages saved");

        return Command::SUCCESS;
    }

}
