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
        for ($k = 1; $k <= $options['max_modif']; $k++) {
            $choices["d12+$k"] = 12 + $k;
        }

        $builder
            ->add('roll', ChoiceType::class, [
                'choices' => $choices,
                'getter' => function (Attribute $attr, FormInterface $form): int {
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
                },
                'expanded' => $options['expanded']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Attribute::class);
        $resolver->setDefault('expanded', false);
        $resolver->setDefault('max_modif', 2);
    }

}
