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
        for ($k = 0; $k < 10; $k++) {
            $iter = new \App\Service\LlmIterator($this->ollamaClient, 'eclipse-phase',
                    "Fais un historique en 7 points d'un écumeur qui vit sur une barge. C'est un homme, un technicien, spécialisé dans la réparation de moteurs à fusion. Un des 7 points doit comporter un evenement tragique et un autre point doit concerner sa famille");

            foreach ($iter as $val) {
                $output->write($val);
            }
            $output->writeln('-------------------------');
            $output->writeln('');
        }

        return self::SUCCESS;
    }

}
