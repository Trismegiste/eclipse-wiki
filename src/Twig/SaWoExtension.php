<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use App\Entity\Ali;
use App\Entity\Character;
use App\Entity\DamageRoll;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Repository\HindranceProvider;
use OutOfBoundsException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\Place;

/**
 * Extension for SaWo specifics
 */
class SaWoExtension extends AbstractExtension
{

    const infoTemplate = [
        Ali::class => 'npc/ali/info.html.twig',
        Transhuman::class => 'npc/transhuman/info.html.twig'
    ];
    const rowTemplate = [
        Ali::class => 'npc/row.html.twig',
        Transhuman::class => 'npc/row.html.twig',
        Vertex::class => 'vertex/row.html.twig',
        Place::class => 'place/row.html.twig'
    ];
    const showTemplate = [
        Ali::class => 'npc/show.html.twig',
        Transhuman::class => 'npc/show.html.twig',
        Vertex::class => 'vertex/show.html.twig',
        Place::class => 'place/show.html.twig'
    ];

    public function getFunctions()
    {
        return [
            new TwigFunction('level_hindrance', [$this, 'printLevelHindrance']),
            new TwigFunction('add_raise', [$this, 'addRaise']),
            new TwigFunction('char_info_template', [$this, 'getInfoTemplate']),
            new TwigFunction('select_row_template', [$this, 'getVertexTemplate']),
            new TwigFunction('dice_icon', [$this, 'diceIcon'], ['is_safe' => ['html']]),
            new TwigFunction('char_icon', [$this, 'iconForCharacter']),
            new TwigFunction('vertex_icon', [$this, 'iconForVertex'])
        ];
    }

    public function addRaise(DamageRoll $damage): DamageRoll
    {
        $damage->addDice(6);

        return $damage;
    }

    public function printLevelHindrance(int $level): string
    {
        return HindranceProvider::paramType[$level];
    }

    public function getInfoTemplate(Character $char): string
    {
        return self::infoTemplate[get_class($char)];
    }

    public function diceIcon(string $roll): string
    {
        $dump = [];
        if (preg_match('#^d(\d{1,2})\+?(\d*)$#', $roll, $dump, PREG_UNMATCHED_AS_NULL)) {
            $str = '<i class="icon-d' . $dump[1] . '"></i>';
            if (!empty($dump[2])) {
                $str .= '+' . $dump[2];
            }

            return $str;
        }

        return $roll;
    }

    public function iconForVertex(Vertex $v): string
    {
        return 'icon-video';
    }

    public function iconForCharacter(Character $v): string
    {
        switch (get_class($v)) {
            case Ali::class:
                return 'icon-ali';
            case Transhuman::class:
                return $v->wildCard ? 'icon-wildcard' : 'icon-extra';
            default :
                throw new OutOfBoundsException("No icon for " . get_class($v));
        }
    }

    public function getVertexTemplate(Vertex $v): string
    {
        return self::rowTemplate[get_class($v)];
    }

}
