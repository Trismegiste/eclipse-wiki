<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use MongoDB\Driver\Command as MongoCommand;
use MongoDB\Driver\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Default config for the mongo database
 */
class CheckDatabase extends Command
{

    protected static $defaultName = 'app:check:mongo';
    protected $dbName;
    protected $manager;

    public function __construct(string $dbName, Manager $man)
    {
        $this->dbName = $dbName;
        $this->manager = $man;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Check the configuration of mongodb');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $io->section("Création de l'index pour l'unicité des title dans la collection 'vertex'");
        $cmd = new MongoCommand([
            'createIndexes' => 'vertex',
            'indexes' => [
                [
                    'name' => "nonDuplicateTitle",
                    'key' => ['title' => 1],
                    'unique' => true
                ]
            ]
        ]);

        $cursor = $this->manager->executeCommand($this->dbName, $cmd);
        $response = $cursor->toArray()[0];

        if ($response->ok) {
            $io->success('Création des index OK');
        } else {
            $io->error($response->note);
        }

        return self::SUCCESS;
    }

}
