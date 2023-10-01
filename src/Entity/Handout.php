<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * A game hand out for PCs with a GM part
 */
class Handout extends Vertex
{

    public string $pcInfo;
    public ?string $gmInfo = null;
    public string $target; // a field for PC (not very defined at current time)

    protected function beforeSave(): void
    {
        parent::beforeSave();
        // not used : just for text search indexing       
        $this->content = <<<WIKITEXT
==PC==
{$this->pcInfo}
==GM==
{$this->gmInfo}
WIKITEXT;
    }

}
