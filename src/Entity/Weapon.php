<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Generic Weapon
 */
class Weapon implements \JsonSerializable
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

    public function jsonSerialize(): array
    {
        $dump = get_object_vars($this);
        $dump['damage'] = DamageRoll::createFromString(str_replace('FOR+', '1', $this->damage));

        return $dump;
    }

}
