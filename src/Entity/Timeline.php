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

    public ?string $elevatorPitch;
    protected PlotNode $tree;
    public ?string $debriefing = null;

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
        $tree = implode(PHP_EOL, $accumul);
        $this->content = <<<WIKITEXT
==Elevator pitch==
{$this->elevatorPitch}
==Timeline==
$tree
==Debriefing==
{$this->debriefing}
WIKITEXT;
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

    public function attachPicture(string $filenameInStorage): void
    {
        // we attach the picture to elevatorPitch, it's convenient when you have pictures and just a draft for timeline
        $this->elevatorPitch .= "\n\n[[file:$filenameInStorage]]\n";
    }

}
