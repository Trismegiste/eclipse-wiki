<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * One way to attack
 */
class Attack implements Persistable
{

    use PersistableImpl;

    public $title;
    public $roll; // a Skill 
    public $rollBonus = 0;
    public $rateOfFire = 1;
    public $damage = []; // an array of sides
    public $damageBonus = 0;
    public $armorPiercing = 0;
    public $reach; // a string

}
