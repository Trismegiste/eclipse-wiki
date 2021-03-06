<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Ali;
use App\Entity\Attribute;
use App\Entity\Background;
use App\Entity\Character;
use App\Entity\Faction;
use App\Entity\Freeform;
use App\Entity\Morph;
use App\Entity\Transhuman;
use RuntimeException;

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
            throw new RuntimeException("Invalid Attributes count");
        }
    }

    public function create(string $title, Background $bg, Faction $fac): Character
    {
        $obj = new Transhuman($title, $bg, $fac);
        $this->addAttributes($obj);

        return $obj;
    }

    public function createAli(string $title): Character
    {
        $char = new Ali($title);
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

    public function createFreeform(string $title, string $type): Character
    {
        $char = new Freeform($title);

        $morph = new Morph('Indissociable');
        $morph->price = 0;
        $morph->type = $type;
        $char->setMorph($morph);

        $this->addAttributes($char);
        foreach ($char->attributes as $attr) {
            $attr->dice = 4;
        }

        return $char;
    }

    public function createExtraFromTemplate(Transhuman $template, string $newName): Transhuman
    {
        $name = $template->getTitle();
        $npc = clone $template;
        $npc->surnameLang = null;
        $npc->wildCard = false;
        $npc->setContent("C'est un [[$name]]");
        $npc->setTitle($newName);

        return $npc;
    }

}
