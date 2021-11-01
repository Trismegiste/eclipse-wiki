<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Description of RangedWeapon
 */
class RangedWeapon extends Weapon
{

    public $reach;
    public $rof;
    public $minStr = 4;

    public function __construct(string $n, string $d, int $a, int $rof, string $reach)
    {
        parent::__construct($n, $d, $a);
        $this->reach = $reach;
        $this->rof = $rof;
    }

}
