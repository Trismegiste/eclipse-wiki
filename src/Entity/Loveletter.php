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

}
