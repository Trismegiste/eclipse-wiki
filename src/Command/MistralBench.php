<?php

/*
 * Eclipse Wiki
 */

namespace App\Command;

use App\Ollama\RequestFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Description of MistralBench
 *
 * @author florent
 */
#[AsCommand(name: 'mistral:bench')]
class MistralBench extends Command
{

    public function __construct(protected HttpClientInterface $ollamaClient, protected RequestFactory $factory)
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('LLM');

        $payload = $this->factory->createBackground();

        for ($k = 0; $k < 10; $k++) {
            $io->title("Historique $k");
            $response = $this->ollamaClient->request('POST', '/api/chat', ['json' => $payload, 'timeout' => 120]);
            $content = json_decode($response->getContent());
            $output->writeln($content->message->content);
        }

        return self::SUCCESS;
    }

}
