<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use App\Attribute\Icon;
use App\Entity\Ali;
use App\Entity\Character;
use App\Entity\DamageRoll;
use App\Entity\Freeform;
use App\Entity\Handout;
use App\Entity\Loveletter;
use App\Entity\Place;
use App\Entity\Scene;
use App\Entity\Timeline;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Repository\HindranceProvider;
use OutOfBoundsException;
use ReflectionAttribute;
use ReflectionObject;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension for SaWo specifics
 */
class SaWoExtension extends AbstractExtension
{

    const editStatTemplate = [
        Ali::class => 'npc/ali/stat.html.twig',
        Transhuman::class => 'npc/transhuman/stat.html.twig',
        Freeform::class => 'npc/freeform/stat.html.twig'
    ];
    const rowTemplate = [
        Ali::class => 'npc/row.html.twig',
        Transhuman::class => 'npc/row.html.twig',
        Freeform::class => 'npc/row.html.twig',
        Vertex::class => 'vertex/row.html.twig',
        Place::class => 'place/row.html.twig',
        Loveletter::class => 'loveletter/row.html.twig',
        Handout::class => 'handout/row.html.twig',
        Scene::class => 'scene/row.html.twig',
        Timeline::class => 'timeline/row.html.twig',
    ];
    const showTemplate = [
        Ali::class => 'npc/ali/show.html.twig',
        Transhuman::class => 'npc/transhuman/show.html.twig',
        Freeform::class => 'npc/freeform/show.html.twig',
        Vertex::class => 'vertex/show.html.twig',
        Place::class => 'place/show.html.twig',
        Loveletter::class => 'loveletter/show.html.twig',
        Handout::class => 'handout/show.html.twig',
        Scene::class => 'scene/show.html.twig',
        Timeline::class => 'timeline/show.html.twig',
    ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction('level_hindrance', [$this, 'printLevelHindrance']),
            new TwigFunction('add_raise', [$this, 'addRaise']),
            new TwigFunction('select_row_template', [$this, 'getVertexTemplate']),
            new TwigFunction('dice_icon', [$this, 'diceIcon'], ['is_safe' => ['html']]),
            new TwigFunction('vertex_icon', [$this, 'iconForVertex']),
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

    public function getVertexTemplate(Vertex $v): string
    {
        return self::rowTemplate[get_class($v)];
    }

    public function iconForVertex(Vertex $v): string
    {
        $refl = new ReflectionObject($v);
        while (0 === count($attr = $refl->getAttributes(Icon::class, ReflectionAttribute::IS_INSTANCEOF))) {
            $refl = $refl->getParentClass();
            if (!$refl) {
                break;
            }
        }
        if (count($attr)) {
            return 'icon-' . $attr[0]->newInstance()->getName($v);
        }

        return 'icon-graph';
    }

}
