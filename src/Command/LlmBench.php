<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'llm:bench')]
class LlmBench extends Command {

    public function __construct(protected HttpClientInterface $ollamaClient)
    {
    }

    public function execute(InputInterface $input, OutputInterface $output): int {

        $request = [
            'model' => 'eclipse-phase',
            'prompt' => "Écris un historique en 7 points",
            'stream' => false
        ];
        $response = $this->algorithmClient->request('POST', '/api/generate', ['json' => $request]);
        $content = json_decode($response->getContent(), true);

        var_dump($content);

	return self::SUCCESS;
    }


}
