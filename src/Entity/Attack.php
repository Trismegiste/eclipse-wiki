<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * One way to attack
 */
class Attack implements Persistable
{

    use PersistableImpl;

    public string $title;
    public $roll; // a Skill 
    public int $rollBonus = 0; // circonstantial bonus
    public int $rateOfFire = 1;
    public $damage;
    public int $armorPiercing = 0;
    public string $reach; // a string : melee, 1, 12/24/48, Cone, MBT...

}
