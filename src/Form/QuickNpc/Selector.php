<?php

/*
 * eclipse-wiki
 */

namespace App\Form\QuickNpc;

use App\Form\NpcCreate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Create a new NPC with the selection of nodes in Creation Graph/Tree
 */
class Selector extends AbstractType
{

    public function getParent()
    {
        return NpcCreate::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('surnameLang')
                ->remove('content');
        $builder->add('edges', EdgeCheckType::class);
    }

}
