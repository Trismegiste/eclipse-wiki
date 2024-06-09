<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * A client for algorithm calculus API (written in Go for speed, about 10× faster than PHP)
 */
class AlgorithmClient
{

    public function __construct(protected HttpClientInterface $algorithmClient)
    {
        
    }

    /**
     * Computes the Floyd-Warshall algorithm to evaluate the distance from all vertices to all vertices
     * @param array $matrix A square matrix for adjacency (passed by reference and returned by reference, for memory concerns)
     * @return void
     */
    public function floydWarshall(array &$matrix): void
    {
        $response = $this->algorithmClient->request('POST', '/algebra/floydwarshall', ['json' => $matrix]);
        $matrix = json_decode($response->getContent(), true);
    }

    /**
     * Brandes algorithm for Intermediary Centrality
     * https://en.wikipedia.org/wiki/Brandes%27_algorithm
     * Call of https://github.com/m-chrzan/brandes multithreaded implementation in C++
     * @param array $matrix
     * @return array
     */
    public function brandesCentrality(array &$matrix): array
    {
        $edge = tmpfile();
        $result = tmpfile();

        foreach ($matrix as $row => $vector) {
            foreach ($vector as $col => $flag) {
                if ($flag) {
                    fprintf($edge, "%d %d\n", $row, $col);
                }
            }
        }
        $brandes = new Process([
            'brandes',
            16,
            stream_get_meta_data($edge)['uri'],
            stream_get_meta_data($result)['uri']
        ]);
        $brandes->mustRun();
        fclose($edge);

        $between = [];
        while ($row = fscanf($result, '%d %f')) {
            list($idx, $centrality) = $row;
            $between[$idx] = $centrality;
        }
        fclose($result);

        return $between;
    }

}
