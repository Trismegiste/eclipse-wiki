<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Repository\VertexRepository;
use App\Service\Storage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Census of unused (unlinked) pictures
 */
#[AsCommand(name: 'picture:unused')]
class PictureCensus extends Command
{

    protected $repo;
    protected $store;

    public function __construct(VertexRepository $repo, Storage $store)
    {
        parent::__construct();
        $this->repo = $repo;
        $this->store = $store;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Search for unused pictures');

        $unused = 0;
        foreach ($this->store->searchByName('(jpg|jpeg|png|webp|gif)') as $pic) {
            $found = $this->repo->searchOne(['$or' => [
                    ['content' => new \MongoDB\BSON\Regex(preg_quote($pic->getBasename()))],
                    ['tokenPic' => $pic->getBasename()]
            ]]);
            if (is_null($found)) {
                $io->writeln($pic->getBasename());
                $unused++;
            }
        }
        $io->comment("$unused files not used");

        return self::SUCCESS;
    }

}
