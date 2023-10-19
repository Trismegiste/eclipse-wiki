<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * A Trait in SaWo
 */
abstract class SaWoTrait implements Indexable
{

    protected string $name;
    public ?int $dice = null;
    public int $modifier = 0; // after d12

    public function __construct(string $str)
    {
        $this->name = $str;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUId(): string
    {
        return $this->name;
    }

}
