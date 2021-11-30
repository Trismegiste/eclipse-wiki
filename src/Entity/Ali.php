<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Ali is an artificial limited intelligence
 */
class Ali extends Character
{

    protected $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return 'IAL';
    }

}
