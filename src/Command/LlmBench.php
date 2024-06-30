<?php

namespace App\Command;

use App\Service\LlmIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
        $io = new SymfonyStyle($input, $output);
        $bench = [];
        for ($k = 0; $k < 10; $k++) {
            $io->title("Historique $k");
            $answer = '';
            $iter = new LlmIterator($this->ollamaClient, 'eclipse-phase',
                    "Fais un historique sur 7 points d'un écumeur qui vit sur une barge. C'est un homme, un technicien, spécialisé dans la réparation de moteurs à fusion. Un des 7 points doit comporter un evenement tragique et un autre point doit concerner sa famille");

            foreach ($iter as $val) {
                $answer .= $val;
            }
            $bench[] = $answer;
            $io->writeln($answer);
        }

        file_put_contents('bench.json', json_encode($bench));

        return self::SUCCESS;
    }

}
