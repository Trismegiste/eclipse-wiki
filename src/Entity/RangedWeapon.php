<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * a Ranged Weapon
 */
class RangedWeapon extends Weapon
{

    public string $reach;
    public int $rof;
    public int $minStr = 4;

    public function __construct(string $n, string $d, int $a, int $rof, string $reach, int $hand)
    {
        parent::__construct($n, $d, $a, $hand);
        $this->reach = $reach;
        $this->rof = $rof;
    }

}
