<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\CreationTree;

use InvalidArgumentException;

/**
 * Creation graph
 */
class Graph implements \JsonSerializable
{

    /** @var Node[] the graph */
    public array $node = [];

    public function getNodeByName(string $name): Node
    {
        foreach ($this->node as $node) {
            if ($name === $node->name) {
                return $node;
            }
        }

        throw new InvalidArgumentException("Node $name is unknown");
    }

    public function deleteNodeAndLinks(Node $current): void
    {
        foreach ($this->node as $entry) {
            /** @var Node $entry */
            $idx = array_search($current->name, $entry->children);
            if (false !== $idx) {
                array_splice($entry->children, $idx, 1);
            }
        }

        $idx = array_search($current, $this->node);
        array_splice($this->node, $idx, 1);
    }

    public function getParentNode(Node $node): array
    {
        $parents = [];
        foreach ($this->node as $entry) {
            /** @var Node $entry */
            if (in_array($node->name, $entry->children)) {
                $parents[] = $entry;
            }
        }

        return $parents;
    }

    public function jsonSerialize(): mixed
    {
        return $this->node;
    }

    public function getShortestDistanceFromAncestor(Node $childSource, Node $ancestorTarget): int
    {
        if ($childSource === $ancestorTarget) {
            return 0;
        }

        $parents = $this->getParentNode($childSource);
        $dist = array_map(function (Node $node) use ($ancestorTarget) {
            return 1 + $this->getShortestDistanceFromAncestor($node, $ancestorTarget);
        }, $parents);

        return ($parents !== []) ? min($dist) : 0;
    }

    public function sortByLevelFromRoot(Node $root): void
    {
        usort($this->node, function (Node $a, Node $b) use ($root) {
            return $this->getShortestDistanceFromAncestor($a, $root) <=> $this->getShortestDistanceFromAncestor($b, $root);
        });
    }

    public function accumulatePromptKeywordPerDistance(Node $root): array
    {
        $dump = [];
        foreach ($this->node as $node) {
            $position = $this->getShortestDistanceFromAncestor($node, $root);
            if (!key_exists($position, $dump)) {
                $dump[$position] = [];
            }

            $dump[$position] = array_unique(array_merge($dump[$position], $node->text2img));
        }

        return $dump;
    }

}
