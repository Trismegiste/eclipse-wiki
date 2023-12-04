<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Loveletter
 */
class Loveletter extends Vertex
{

    public string $player;
    public string $context;
    public string $drama;
    public $roll1;
    public $roll2;
    public $roll3;
    public array $resolution = [];
    public array $pcChoice = [];

    protected function beforeSave(): void
    {
        parent::beforeSave();
        // not used : just for text search indexing
        $this->content = <<<WIKITEXT
==Context==
{$this->context}
==Drama==
{$this->drama}
WIKITEXT;
    }

    public function attachPicture(string $filenameInStorage): void
    {
        $this->drama .= "\n\n[[file:$filenameInStorage]]\n";
    }

    public function renameInternalLink(string $oldTitle, string $newTitle): void
    {
        $regex = "#\[\[" . static::getFirstLetterCaseInsensitiveRegexPart($oldTitle) . "(\]\]|\|)#";
        $this->context = preg_replace($regex, "[[$newTitle" . '$1', $this->context);
        $this->drama = preg_replace($regex, "[[$newTitle" . '$1', $this->drama);
    }


}
