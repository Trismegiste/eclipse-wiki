<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * A client for algorithm calculus API (written in Go for speed, about 10× faster than PHP)
 */
class AlgorithmClient
{

    public function __construct(protected HttpClientInterface $client)
    {
        
    }

    /**
     * Computes the Floyd-Warshall algorithm to evaluate the distance from all vertices to all vertices
     * @param array $matrix A square matrix for adjacency (passed by reference and returned by reference, for memory concerns)
     * @return void
     */
    public function floydWarshall(array &$matrix): void
    {
        $response = $this->client->request('POST', 'http://localhost:3333/algebra/floydwarshall', [// @todo remove hardcoded value
            'json' => $matrix
        ]);
        $matrix = json_decode($response->getContent(), true);
    }

}
