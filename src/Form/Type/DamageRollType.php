<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\DamageRoll;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DamageRollType extends AbstractType
{

    private $transformer;

    public function __construct(DamageRollTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

}
