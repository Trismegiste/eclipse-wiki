<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use App\Attribute\Icon;

/**
 * Loveletter
 */
#[Icon('loveletter')]
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
        // not used : just for text search indexing
        $this->content = <<<WIKITEXT
==Context==
{$this->context}
==Drama==
{$this->drama}
WIKITEXT;
        // dump outbound links
        parent::beforeSave();
    }

    public function attachPicture(string $filenameInStorage): void
    {
        $this->drama .= "\n\n[[file:$filenameInStorage]]\n";
    }

    public function renameInternalLink(string $oldTitle, string $newTitle): void
    {
        $this->context = static::replaceInternalLinkFirstCharCaseInsensitive($this->context, $oldTitle, $newTitle);
        $this->drama = static::replaceInternalLinkFirstCharCaseInsensitive($this->drama, $oldTitle, $newTitle);
    }

}
