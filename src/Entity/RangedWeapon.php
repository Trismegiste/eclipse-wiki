<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Description of RangedWeapon
 */
class RangedWeapon implements \JsonSerializable
{

    public $name;
    public $damage;
    public $ap;
    public $reach;
    public $rof;

    public function __construct(string $n, string $d, int $a, int $rof, string $reach)
    {
        $this->name = $n;
        $this->damage = $d;
        $this->ap = $a;
        $this->reach = $reach;
        $this->rof = $rof;
    }

    public function jsonSerialize(): array
    {
        $dump = get_object_vars($this);
        $dump['damage'] = DamageRoll::createFromString($this->damage);

        return $dump;
    }

}
