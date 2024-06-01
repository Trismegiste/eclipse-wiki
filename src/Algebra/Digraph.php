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
    }

    public function setAdjacency(GraphEdgeIterator $iter): void
    {
        $this->idx2pk = array_keys($this->vertex);
        $this->pk2idx = array_flip($this->idx2pk);

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

}
