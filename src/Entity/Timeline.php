<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Timeline of events
 */
class Timeline extends Vertex
{

    protected PlotNode $tree;

    public function getTree(): PlotNode
    {
        return $this->tree;
    }

    public function setTree(PlotNode $tree): void
    {
        $this->tree = $tree;
    }

    protected function beforeSave(): void
    {
        parent::beforeSave();
        $accumul = [];
        $this->flattenTree($accumul, $this->tree->nodes, 1);
        $this->content = implode(PHP_EOL, $accumul);
    }

    protected function flattenTree(array &$accumul, array $children, int $level): void
    {
        foreach ($children as $child) {
            $label = $child->title;
            if ($child->finished) {
                $label = "<strike>$label</strike>";
            }
            $accumul[] = str_repeat('*', $level) . ' ' . $label;
            $this->flattenTree($accumul, $child->nodes, $level + 1);
        }
    }

}
