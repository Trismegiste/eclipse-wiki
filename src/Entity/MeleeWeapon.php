<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Description of MeleeWeapon
 */
class MeleeWeapon
{

    public $name;
    public $damage;
    public $ap;

    public function __construct(string $n, string $d, int $a)
    {
        $this->name = $n;
        $this->damage = $d;
        $this->ap = $a;
    }

}
