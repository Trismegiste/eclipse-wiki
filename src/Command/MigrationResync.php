<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Repository\VertexRepository;
use MongoDB\BSON\Persistable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trismegiste\Strangelove\MongoDb\Root;

/**
 * Resynchronize database with class model
 */
#[AsCommand(name: 'db:resync:model')]
class MigrationResync extends Command
{

    protected array $logging = [];

    public function __construct(protected VertexRepository $repo)
    {
        parent::__construct();
        $this->repo = $repo;
    }

    protected function configure(): void
    {
        $this->addOption('remove', null, InputOption::VALUE_NONE, 'Remove fields missing in the model')
                ->setDescription('Update database by removing fields not included in the model (only Root entities)');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        set_error_handler([$this, 'interceptDynamicPropertyNotice'], E_DEPRECATED);

        $io = new SymfonyStyle($input, $output);
        $io->title("Dynamic properties searching");

        $iter = $this->repo->search();

        foreach ($iter as $vertex) {
            
        }

        $this->logging = array_unique($this->logging);
        $io->table(['class', 'property'], $this->logging);

        foreach ($this->logging as $entry) {
            if (is_subclass_of($entry['class'], Root::class, true)) {
                $io->writeln("== Search for " . $entry['class'] . '::' . $entry['property'] . ' ==');
                $iter = $this->repo->searchFieldExistsForClass($entry['class'], $entry['property']);
                foreach ($iter as $vertex) {
                    $io->write($vertex->getPk() . ' - ' . $entry['class'] . '::' . $entry['property']);
                    if ($input->getOption('remove')) {
                        $this->repo->removeField($vertex->getPk(), $entry['property']);
                        $io->writeln(' : removed');
                    }else {
                        $io->newLine();
                    }
                }
            }
        }

        return self::SUCCESS;
    }

    public function interceptDynamicPropertyNotice($errno, $errstr, $errfile, $errline)
    {
        if (($errno === E_DEPRECATED) &&
                (preg_match('#^Creation of dynamic property ([^:]+)::\$([\S]+) is deprecated$#', $errstr, $match)) &&
                (is_subclass_of($match[1], Persistable::class, true))) {
            $this->logging[] = ['class' => $match[1], 'property' => $match[2]];

            return true;
        }

        return false;
    }

}
