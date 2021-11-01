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
        $obj = new \App\Entity\Transhuman($bg, $fac);
        $this->addAttributes($obj);

        return $obj;
    }

    public function createAli(): Character
    {
        $char = new \App\Entity\Ali();
        $this->addAttributes($char);
        foreach ($char->attributes as $attr) {
            $attr->dice = 4;
        }

        return $char;
    }

    protected function addAttributes(Character $obj): void
    {
        foreach ($this->attributes as $label) {
            $obj->attributes[] = new Attribute($label);
        }
    }

}
