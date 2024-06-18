<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'llm:bench')]
class LlmBench extends Command
{

    public function __construct(protected HttpClientInterface $ollamaClient)
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $iter = new \App\Service\LlmIterator($this->ollamaClient, 'eclipse-phase', "Écris-moi une histoire dramatique en 5 actes");

        foreach ($iter as $val) {
            $output->write($val);
        }
        $output->writeln('');

        return self::SUCCESS;
    }

}
