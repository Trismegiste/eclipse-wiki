<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * A Trait in SaWo
 */
class SaWoTrait
{

    protected $name;
    public $dice;
    public $modifier = 0; // after d12

    public function __construct(string $str)
    {
        $this->name = $str;
    }

    public function getName(): string
    {
        return $this->name;
    }

}
