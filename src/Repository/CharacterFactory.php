<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Attribute;
use App\Entity\Background;
use App\Entity\Character;
use App\Entity\Faction;

/**
 * Description of CharacterFactory
 */
class CharacterFactory
{

    protected $attributes = [];

    public function __construct(TraitProvider $pro, int $attrCount = 5)
    {
        $this->attributes = array_values($pro->findAttributes());
        if (count($this->attributes) !== $attrCount) {
            throw new \RuntimeException("Invalid Attributes count");
        }
    }

    public function create(Background $bg, Faction $fac): Character
    {
        $obj = new Character($bg, $fac);
        foreach ($this->attributes as $label) {
            $obj->attributes[] = new Attribute($label);
        }

        return $obj;
    }

}
