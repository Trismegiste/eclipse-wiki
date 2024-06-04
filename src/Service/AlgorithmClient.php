<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

/**
 * A frontend for algorithm calculus
 */
class AlgorithmClient
{

    public function __construct(protected \Symfony\Contracts\HttpClient\HttpClientInterface $client)
    {
        
    }

    public function floydWarshall(array &$matrix): void
    {
        $response = $this->client->request('POST', 'http://localhost:3333/algebra/floydwarshall', [
            'json' => $matrix
        ]);
        $matrix = json_decode($response->getContent(), true);
    }

}
