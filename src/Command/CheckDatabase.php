<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use MongoDB\Driver\Command as MongoCommand;
use MongoDB\Driver\Manager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Default config for the mongo database
 */
#[AsCommand(name: 'db:install')]
class CheckDatabase extends Command
{

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
        $this->setDescription('Install config for MongoDb');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $io->section("Indices creation for 'vertex' collection");
        $cmd = new MongoCommand([
            'createIndexes' => 'vertex',
            'indexes' => [
                [
                    'name' => "nonDuplicateTitle",
                    'key' => ['title' => 1],
                    'unique' => true
                ],
                [
                    'name' => 'FullTextSearch',
                    'key' => ['title' => 'text', 'content' => 'text'],
                    'weights' => ['title' => 5],
                    'default_language' => "french"
                ]
            ]
        ]);

        $cursor = $this->manager->executeCommand($this->dbName, $cmd);
        $response = $cursor->toArray()[0];

        if ($response->ok) {
            $io->success('Indices creation OK');
        } else {
            $io->error($response->note);
        }

        return self::SUCCESS;
    }

}
