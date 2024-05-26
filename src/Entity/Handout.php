<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use App\Attribute\Icon;

/**
 * A game hand out for PCs with a GM part
 */
#[Icon('handout')]
class Handout extends Vertex
{

    public string $pcInfo;
    public ?string $gmInfo = null;
    public string $target; // a field for PC (not very defined at current time)

    protected function beforeSave(): void
    {
        // not used : just for text search indexing       
        $this->content = <<<WIKITEXT
==PC==
{$this->pcInfo}
==GM==
{$this->gmInfo}
WIKITEXT;
        // dump outbound links
        parent::beforeSave();
    }

    public function attachPicture(string $filenameInStorage): void
    {
        $this->gmInfo .= "\n\n[[file:$filenameInStorage]]\n";
    }

    public function renameInternalLink(string $oldTitle, string $newTitle): void
    {
        $this->pcInfo = static::replaceInternalLinkFirstCharCaseInsensitive($this->pcInfo, $oldTitle, $newTitle);
        $this->gmInfo = static::replaceInternalLinkFirstCharCaseInsensitive($this->gmInfo, $oldTitle, $newTitle);
    }

}
