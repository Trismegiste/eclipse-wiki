<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Repository\VertexRepository;
use App\Service\Storage;
use MongoDB\BSON\Regex;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    protected function configure()
    {
        $this->addOption('purge', null, InputOption::VALUE_NONE, 'Delete pictures');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Search for unused pictures');

        $unused = 0;
        foreach ($this->store->searchByName('(jpg|jpeg|png|webp|gif|svg|json)') as $pic) {
            /** @var \SplFileInfo $pic */
            // we can find picture :
            $found = $this->repo->searchOne(['$or' => [
                    // in all vertices content
                    ['content' => new Regex(preg_quote($pic->getBasename()))],
                    // in character avatar
                    ['tokenPic' => $pic->getBasename()],
                    // in loveletter drama field
                    ['drama' => new Regex(preg_quote($pic->getBasename()))],
                    // in handout gm info field
                    ['gmInfo' => new Regex(preg_quote($pic->getBasename()))],
                    // in place battlemap field
                    ['battlemap3d' => $pic->getBasename()],
            ]]);
            if (is_null($found)) {
                $io->writeln($pic->getBasename());
                $unused++;
                if ($input->getOption('purge')) {
                    @unlink($pic->getPathname());
                }
            }
        }
        $io->comment("$unused files not used");

        return self::SUCCESS;
    }

}
