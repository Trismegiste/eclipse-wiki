<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Attribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of AttributeType
 */
class AttributeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        for ($k = 4; $k <= 12; $k += 2) {
            $choices["d$k"] = $k;
        }
        $choices['d12+1'] = 13;
        $choices['d12+2'] = 14;

        $builder
            ->add('roll', ChoiceType::class, [
                'choices' => $choices,
                'getter' => function (Attribute $attr, FormInterface $form): bool {
                    return $attr->dice + $attr->modifier;
                },
                'setter' => function (Attribute &$attr, int $roll, FormInterface $form): void {
                    if ($roll > 12) {
                        $attr->dice = 12;
                        $attr->modifier = $roll - 12;
                    } else {
                        $attr->dice = $roll;
                        $attr->modifier = 0;
                    }
                }
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Attribute::class);
    }

}
