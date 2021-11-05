<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use App\Entity\DamageRoll;
use App\Repository\HindranceProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension for SaWo specifics
 */
class SaWoExtension extends AbstractExtension
{

    const infoTemplate = [
        \App\Entity\Ali::class => 'npc/info_ali.html.twig',
        \App\Entity\Transhuman::class => 'npc/info_transhuman.html.twig'
    ];

    public function getFunctions()
    {
        return [
            new TwigFunction('level_hindrance', [$this, 'printLevelHindrance']),
            new TwigFunction('add_raise', [$this, 'addRaise']),
            new TwigFunction('char_info_template', [$this, 'getInfoTemplate']),
            new TwigFunction('dice_icon', [$this, 'diceIcon'], ['is_safe' => ['html']])
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

    public function getInfoTemplate(\App\Entity\Character $char): string
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

}
