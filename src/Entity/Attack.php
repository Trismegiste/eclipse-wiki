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

    public $title;
    public $roll; // a Skill 
    public $rollBonus = 0; // circonstantial bonus
    public $rateOfFire = 1;
    public $damage;
    public $armorPiercing = 0;
    public $reach; // a string : melee, 1, 12/24/48, Cone, MBT...

}
