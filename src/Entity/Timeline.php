<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use App\Attribute\Icon;

/**
 * Timeline of events
 */
#[Icon('movie-roll')]
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
        // dump outbound links
        parent::beforeSave();
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

    /**
     * When a vertex is renamed, backlinks should be renamed also. This method explores the tree and search for those backlinks
     * Because renaming the content (default behavior) has no effet since it is generated each time the object is persisted
     * @param string $oldTitle
     * @param string $newTitle
     * @return void
     */
    public function renameInternalLink(string $oldTitle, string $newTitle): void
    {
        $this->recursivRenameInternalLink($this->tree, $oldTitle, $newTitle);
    }

    protected function recursivRenameInternalLink(PlotNode $node, string $oldTitle, string $newTitle): void
    {
        $regex = "#\[\[" . static::getFirstLetterCaseInsensitiveRegexPart($oldTitle) . "(\]\]|\|)#u";
        $node->title = preg_replace($regex, "[[$newTitle" . '$1', $node->title);
        foreach ($node->nodes as $child) {
            $this->recursivRenameInternalLink($child, $oldTitle, $newTitle);
        }
    }

}
