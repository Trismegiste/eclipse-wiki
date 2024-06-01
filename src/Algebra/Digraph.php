<?php

/*
 * Eclipse Wiki
 */

namespace App\Algebra;

/**
 * Description of Graph
 *
 * @author florent
 */
class Digraph
{

    public array $vertex;
    public array $adjacency;
    protected array $idx2pk;
    protected array $pk2idx;

    public function __construct(GraphVertexIterator $vertex)
    {
        $this->vertex = iterator_to_array($vertex);
        $this->idx2pk = array_keys($this->vertex);
        $this->pk2idx = array_flip($this->idx2pk);
    }

    public function setAdjacency(GraphEdgeIterator $iter): void
    {
        // initialize adjacency matrix
        $this->adjacency = [];
        $side = count($this->vertex);
        $row = array_fill(0, $side, false);
        for ($k = 0; $k < $side; $k++) {
            $this->adjacency[$k] = $row;
        }

        // insert edges from iterator
        foreach ($iter as $edge) {
            $this->adjacency[$this->pk2idx[$edge->source]][$this->pk2idx[$edge->target]] = true;
        }
    }

    public function extractVectorByCategory(string $category): array
    {
        $vector = [];
        foreach ($this->vertex as $pk => $vertex) {
            if ($vertex->category === $category) {
                $vector[$this->pk2idx[$pk]] = $vertex;
            }
        }

        return $vector;
    }

    public function getVertexByIndex(int $idx): GraphVertex
    {
        return $this->vertex[$this->idx2pk[$idx]];
    }

}
