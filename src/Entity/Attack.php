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
    public $roll; // a skill 
    public $rollBonus = 0;
    public $rateOfFire = 1;
    public $damage = ['pool' => [], 'bonus' => 0]; // an array of sides and a modifier
    public $armorPiercing = 0;
    public $reach; // a string

}
