<?php

/*
 * eclipse-wiki
 */

namespace App\Form\QuickNpc;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of SocNetHiddenStat
 *
 * @author trismegiste
 */
class SocNetHiddenStat extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('key', TextType::class, ['required' => false])
                ->add('value', IntegerType::class, ['required' => false])
        ;
    }

}
