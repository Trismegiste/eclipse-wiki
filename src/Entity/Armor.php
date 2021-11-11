<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * Description of Armor
 */
class Armor implements Persistable, Indexable
{

    use PersistableImpl;

    public $name;
    public $protect;
    public $special;
    public $zone;

    public function __construct(string $name = '', int $protect = 0, string $spe = '', string $z = '')
    {
        $this->name = $name;
        $this->protect = $protect;
        $this->special = $spe;
        $this->zone = $z;
    }

    public function getUId(): string
    {
        return $this->name;
    }

}
