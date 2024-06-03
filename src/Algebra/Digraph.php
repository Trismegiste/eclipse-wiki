<?php

/*
 * Eclipse Wiki
 */

namespace App\Algebra;

use Iterator;

/**
 * A digraph for the wiki
 */
class Digraph
{

    public array $vertex;
    public array $adjacency;
    protected array $idx2pk;
    protected array $pk2idx;

    /**
     * Creates a neW digraph with an iterator on GraphVertex, the key of the iterator should be the primary key of the vertex
     * @param Iterator $vertex
     */
    public function __construct(Iterator $vertex)
    {
        $this->vertex = iterator_to_array($vertex);
        $this->idx2pk = array_keys($this->vertex);
        $this->pk2idx = array_flip($this->idx2pk);
    }

    /**
     * Initializes the adjacency matrix with a iterator on GraphEdge
     * @param Iterator $iter
     * @return void
     */
    public function setAdjacency(Iterator $iter): void
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

    /**
     * Extracts a partial vector of Vertices for one category. Keeps the algebric index of the vertex, therefore could have holes in indexing
     * @param string $category
     * @return array
     */
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

    /**
     * Gets a vertex in the graph by its algebraic index
     * @param int $idx
     * @return GraphVertex
     */
    public function getVertexByIndex(int $idx): GraphVertex
    {
        return $this->vertex[$this->idx2pk[$idx]];
    }

    public function createUndirectedAdjacency(): array
    {
        $matrix = [];
        foreach ($this->adjacency as $row => $vector) {
            foreach ($vector as $col => $flag) {
                $matrix[$row][$col] = $flag || $this->adjacency[$col][$row];
            }
        }

        return $matrix;
    }

    /**
     * Gets an array of vertices connected (inbound and outbound) to a given vertex given by its primary key
     * @param string $pk
     * @return array
     */
    public function getConnectedVertex(string $pk): array
    {
        $connected = [];
        $focusIdx = $this->pk2idx[$pk];
        // stack outbound vertices
        foreach ($this->adjacency[$focusIdx] as $idx => $flag) {
            if ($flag) {
                $connected[$idx] = $this->vertex[$this->idx2pk[$idx]];
            }
        }
        // stack inbound vertices
        foreach (array_column($this->adjacency, $focusIdx) as $idx => $flag) {
            if ($flag) {
                $connected[$idx] = $this->vertex[$this->idx2pk[$idx]];
            }
        }

        return $connected;
    }

    /**
     * Searches for the list of orphan vertices (no inbound and outbound edges)
     * @return array
     */
    public function searchOrphan(): array
    {
        $orphan = [];
        $undirected = $this->createUndirectedAdjacency();

        foreach ($undirected as $source => $row) {
            $isOrphan = true;
            // check vertices
            foreach ($row as $target => $flag) {
                if ($flag && ($target !== $source)) {
                    $isOrphan = false;
                    break;
                }
            }
            if ($isOrphan) {
                $orphan[] = $this->getVertexByIndex($source);
            }
        }

        return $orphan;
    }

}
